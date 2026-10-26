<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\Product;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use App\Models\VendorTransaction;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    protected function getVendor(): VendorProfile
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'No vendor store associated with this user account.');
        }
        return $vendor;
    }

    public function dashboard()
    {
        $vendor = $this->getVendor();

        $todayStart = Carbon::today();
        $todaySales = Order::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_DELIVERED])
            ->where('created_at', '>=', $todayStart)
            ->sum('vendor_earnings_amount');

        $incomingCount = Order::where('vendor_profile_id', $vendor->id)
            ->where('status', Order::STATUS_PENDING_VENDOR)
            ->count();

        $activeOrdersCount = Order::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', [Order::STATUS_ACCEPTED, Order::STATUS_PREPARING, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERY_REQUESTED, Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_OUT_FOR_DELIVERY])
            ->count();

        $totalCompleted = Order::where('vendor_profile_id', $vendor->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();

        $recentOrders = Order::with(['customer', 'items'])
            ->where('vendor_profile_id', $vendor->id)
            ->latest('id')
            ->take(6)
            ->get();

        $walletBalance = $vendor->wallet_balance;

        return view('vendor.dashboard', compact(
            'vendor',
            'todaySales',
            'incomingCount',
            'activeOrdersCount',
            'totalCompleted',
            'recentOrders',
            'walletBalance'
        ));
    }

    public function orders(Request $request)
    {
        $vendor = $this->getVendor();
        $status = $request->get('status');

        $query = Order::with(['customer', 'deliveryAgent', 'items'])
            ->where('vendor_profile_id', $vendor->id);

        if ($status === 'incoming') {
            $query->where('status', Order::STATUS_PENDING_VENDOR);
        } elseif ($status === 'active') {
            $query->whereIn('status', [
                Order::STATUS_ACCEPTED,
                Order::STATUS_PREPARING,
                Order::STATUS_READY_FOR_PICKUP,
                Order::STATUS_DELIVERY_REQUESTED,
                Order::STATUS_DELIVERY_ASSIGNED,
                Order::STATUS_OUT_FOR_DELIVERY,
            ]);
        } elseif ($status === 'completed') {
            $query->where('status', Order::STATUS_DELIVERED);
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', [Order::STATUS_CANCELLED, Order::STATUS_REJECTED]);
        }

        $orders = $query->latest('id')->paginate(10)->withQueryString();

        return view('vendor.orders', compact('vendor', 'orders', 'status'));
    }

    public function orderDetails(int $id)
    {
        $vendor = $this->getVendor();
        $order = Order::with(['customer', 'deliveryAgent', 'items.product', 'deliveryAddress', 'statusHistory'])
            ->where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        // Fetch Pickup OTP record for this store to display upon agent arrival
        $pickupOtpRecord = OtpVerification::where('order_id', $order->id)
            ->where('stage', OtpVerification::STAGE_PICKUP)
            ->where('is_verified', false)
            ->latest('id')
            ->first();

        return view('vendor.order-details', compact('vendor', 'order', 'pickupOtpRecord'));
    }

    public function acceptOrder(int $id)
    {
        $vendor = $this->getVendor();
        $order = Order::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $result = $this->orderService->vendorAccept($order, $vendor);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('vendor.orders.show', $order->id)
            ->with('success', $result['message']);
    }

    public function rejectOrder(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $vendor = $this->getVendor();
        $order = Order::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $result = $this->orderService->vendorReject($order, $vendor, $validated['rejection_reason']);

        return redirect()->route('vendor.orders')
            ->with('info', $result['message']);
    }

    public function startPreparing(int $id)
    {
        $vendor = $this->getVendor();
        $order = Order::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $result = $this->orderService->vendorStartPreparing($order, $vendor);

        return back()->with('success', $result['message']);
    }

    public function readyForPickup(int $id)
    {
        $vendor = $this->getVendor();
        $order = Order::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $result = $this->orderService->vendorReadyForPickup($order, $vendor);

        return back()->with('success', $result['message']);
    }

    public function resendPickupOtp(int $id)
    {
        $vendor = $this->getVendor();
        $order = Order::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $result = $this->orderService->resendPickupOtp($order, auth()->user());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function products()
    {
        $vendor = $this->getVendor();
        $products = Product::with('category')
            ->where('vendor_profile_id', $vendor->id)
            ->latest('id')
            ->paginate(15);

        return view('vendor.products.index', compact('vendor', 'products'));
    }

    public function createProduct()
    {
        $vendor = $this->getVendor();
        $categories = Category::active()->get();
        return view('vendor.products.create', compact('vendor', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $vendor = $this->getVendor();

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'vendor_profile_id' => $vendor->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'],
            'deposit_amount' => $validated['deposit_amount'] ?? 0.00,
            'prep_time_minutes' => $validated['prep_time_minutes'] ?? 10,
            'is_active' => true,
            'is_approved' => true,
            'is_available' => true,
        ]);

        InventoryLog::create([
            'product_id' => $product->id,
            'vendor_profile_id' => $vendor->id,
            'quantity_change' => $product->stock_quantity,
            'previous_quantity' => 0,
            'new_quantity' => $product->stock_quantity,
            'type' => 'restock',
            'notes' => 'Initial stock on creation',
        ]);

        return redirect()->route('vendor.products.index')->with('success', 'Product added successfully.');
    }

    public function editProduct(int $id)
    {
        $vendor = $this->getVendor();
        $product = Product::where('vendor_profile_id', $vendor->id)->findOrFail($id);
        $categories = Category::active()->get();

        return view('vendor.products.edit', compact('vendor', 'product', 'categories'));
    }

    public function updateProduct(Request $request, int $id)
    {
        $vendor = $this->getVendor();
        $product = Product::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $prevStock = $product->stock_quantity;
        $newStock = (int) $validated['stock_quantity'];

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'stock_quantity' => $newStock,
            'unit' => $validated['unit'],
            'deposit_amount' => $validated['deposit_amount'] ?? 0.00,
            'prep_time_minutes' => $validated['prep_time_minutes'] ?? 10,
            'is_available' => $request->boolean('is_available'),
        ]);

        if ($prevStock !== $newStock) {
            InventoryLog::create([
                'product_id' => $product->id,
                'vendor_profile_id' => $vendor->id,
                'quantity_change' => $newStock - $prevStock,
                'previous_quantity' => $prevStock,
                'new_quantity' => $newStock,
                'type' => 'manual_adjustment',
                'notes' => 'Stock updated from vendor dashboard',
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function toggleStoreStatus(Request $request)
    {
        $vendor = $this->getVendor();

        if ($request->has('is_open')) {
            $vendor->is_open = $request->boolean('is_open');
        }

        if ($request->has('is_accepting_orders')) {
            $vendor->is_accepting_orders = $request->boolean('is_accepting_orders');
        }

        $vendor->save();

        return back()->with('success', 'Store operational availability updated.');
    }

    public function earnings()
    {
        $vendor = $this->getVendor();
        $transactions = VendorTransaction::with('order')
            ->where('vendor_profile_id', $vendor->id)
            ->latest('id')
            ->paginate(15);

        $settlements = VendorSettlement::where('vendor_profile_id', $vendor->id)
            ->latest('id')
            ->get();

        $walletBalance = $vendor->wallet_balance;

        return view('vendor.earnings', compact('vendor', 'transactions', 'settlements', 'walletBalance'));
    }

    public function requestSettlement(Request $request)
    {
        $vendor = $this->getVendor();
        $balance = $vendor->wallet_balance;

        if ($balance < 500) {
            return back()->withErrors(['settlement' => 'Minimum wallet balance for payout request is ₹500.00.']);
        }

        VendorSettlement::create([
            'vendor_profile_id' => $vendor->id,
            'settlement_reference' => 'SETTLE-' . strtoupper(Str::random(10)),
            'amount' => $balance,
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'status' => 'pending',
            'notes' => 'Payout requested by vendor.',
        ]);

        return back()->with('success', 'Settlement request submitted to administration for payout processing.');
    }
}
