<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryAgentProfile;
use App\Models\DeliveryFeeRule;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalOrders = Order::count();
        $totalGmv = Order::whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_DELIVERED])
            ->sum('total_amount');

        $totalCommissions = Order::where('status', Order::STATUS_DELIVERED)
            ->sum('platform_commission_amount');

        $activeVendors = VendorProfile::where('status', 'approved')->count();
        $pendingVendors = VendorProfile::where('status', 'pending')->count();
        $activeAgents = DeliveryAgentProfile::where('status', 'approved')->count();
        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();

        $recentOrders = Order::with(['customer', 'vendor', 'deliveryAgent'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalGmv',
            'totalCommissions',
            'activeVendors',
            'pendingVendors',
            'activeAgents',
            'totalCustomers',
            'recentOrders'
        ));
    }

    public function orders(Request $request)
    {
        $status = $request->get('status');
        $query = Order::with(['customer', 'vendor', 'deliveryAgent']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'status'));
    }

    public function orderDetails(int $id)
    {
        $order = Order::with([
            'customer',
            'vendor',
            'deliveryAgent',
            'items.product',
            'deliveryAddress',
            'pickupAddress',
            'statusHistory.actor',
            'vendorAssignments.vendor',
            'deliveryAssignments.deliveryAgent',
            'otpVerifications',
            'payments',
            'refunds',
            'review',
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function vendors()
    {
        $vendors = VendorProfile::with(['user', 'primaryCategory'])
            ->latest('id')
            ->paginate(15);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function updateVendorStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,suspended,pending'],
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $vendor = VendorProfile::findOrFail($id);
        $oldStatus = $vendor->status;
        $vendor->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        AuditLog::log(
            "vendor_status_change",
            VendorProfile::class,
            $vendor->id,
            ['status' => $validated['status']],
            ['status' => $oldStatus]
        );

        return back()->with('success', "Vendor status updated to {$validated['status']}.");
    }

    public function agents()
    {
        $agents = DeliveryAgentProfile::with('user')
            ->latest('id')
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    public function updateAgentStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,suspended,pending'],
        ]);

        $agent = DeliveryAgentProfile::findOrFail($id);
        $agent->update(['status' => $validated['status']]);

        AuditLog::log("agent_status_change", DeliveryAgentProfile::class, $agent->id, ['status' => $validated['status']]);

        return back()->with('success', "Agent status updated to {$validated['status']}.");
    }

    public function categories()
    {
        $categories = Category::withCount('products')
            ->orderBy('display_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'bi-tag-fill',
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    public function settlements()
    {
        $settlements = VendorSettlement::with('vendor')
            ->latest('id')
            ->paginate(15);

        return view('admin.settlements.index', compact('settlements'));
    }

    public function updateSettlement(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:paid,rejected,processing'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $settlement = VendorSettlement::findOrFail($id);
        $settlement->update([
            'status' => $validated['status'],
            'payment_reference' => $validated['payment_reference'] ?? $settlement->payment_reference,
            'notes' => $validated['notes'] ?? $settlement->notes,
            'processed_at' => ($validated['status'] === 'paid') ? now() : null,
        ]);

        return back()->with('success', "Settlement updated to {$validated['status']}.");
    }

    public function settings()
    {
        $settings = Setting::all()->groupBy('group');
        $deliveryRule = DeliveryFeeRule::first();

        return view('admin.settings.index', compact('settings', 'deliveryRule'));
    }

    public function updateSettings(Request $request)
    {
        $settingsData = $request->input('settings', []);
        foreach ($settingsData as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->has('delivery_fee')) {
            $df = $request->input('delivery_fee');
            $rule = DeliveryFeeRule::first();
            if ($rule) {
                $rule->update([
                    'base_fee' => $df['base_fee'] ?? 25.00,
                    'per_km_fee' => $df['per_km_fee'] ?? 8.00,
                    'free_delivery_threshold' => $df['free_delivery_threshold'] ?? 500.00,
                ]);
            }
        }

        return back()->with('success', 'Platform settings and delivery fee rules updated successfully.');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest('id')->paginate(20);
        return view('admin.audit-logs', compact('logs'));
    }
}
