<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\Review;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function dashboard()
    {
        $user = Auth::user();
        $recentOrders = Order::with(['vendor', 'items'])
            ->where('customer_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get();

        $activeOrders = Order::with(['vendor', 'deliveryAgent'])
            ->where('customer_id', $user->id)
            ->whereNotIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])
            ->get();

        return view('customer.dashboard', compact('user', 'recentOrders', 'activeOrders'));
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::with(['vendor', 'items'])
            ->where('customer_id', $user->id)
            ->latest('id')
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetails(int $id)
    {
        $user = Auth::user();
        $order = Order::with([
            'vendor',
            'deliveryAgent',
            'items.product',
            'deliveryAddress',
            'pickupAddress',
            'statusHistory',
            'review',
        ])
            ->where('customer_id', $user->id)
            ->findOrFail($id);

        // If order is out for delivery, get the customer's delivery confirmation OTP
        $deliveryOtp = null;
        if ($order->status === Order::STATUS_OUT_FOR_DELIVERY) {
            $otpRecord = OtpVerification::where('order_id', $order->id)
                ->where('stage', OtpVerification::STAGE_DELIVERY)
                ->where('is_verified', false)
                ->latest('id')
                ->first();
            // Note: plain OTP was generated; we also display notification reminder
        }

        return view('customer.order-details', compact('order', 'deliveryOtp'));
    }

    public function cancelOrder(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $order = Order::where('customer_id', $user->id)->findOrFail($id);

        $result = $this->orderService->cancelOrder($order, $user, $validated['reason']);

        if (!$result['success']) {
            return back()->withErrors(['cancel' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function resendDeliveryOtp(int $id)
    {
        $user = Auth::user();
        $order = Order::where('customer_id', $user->id)->findOrFail($id);

        $result = $this->orderService->resendDeliveryOtp($order, $user);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    public function submitReview(Request $request, int $id)
    {
        $validated = $request->validate([
            'vendor_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'vendor_review' => ['nullable', 'string', 'max:1000'],
            'delivery_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'delivery_review' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $order = Order::where('customer_id', $user->id)->findOrFail($id);

        if ($order->status !== Order::STATUS_DELIVERED) {
            return back()->withErrors(['review' => 'You can only review completed orders.']);
        }

        Review::updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => $user->id,
                'vendor_profile_id' => $order->vendor_profile_id,
                'delivery_agent_id' => $order->delivery_agent_id,
                'vendor_rating' => $validated['vendor_rating'],
                'vendor_review' => $validated['vendor_review'] ?? null,
                'delivery_rating' => $validated['delivery_rating'] ?? null,
                'delivery_review' => $validated['delivery_review'] ?? null,
                'is_approved' => true,
            ]
        );

        return back()->with('success', 'Thank you for your rating and review!');
    }

    public function addresses()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();
        return view('customer.addresses', compact('addresses'));
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:15'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();

        if ($request->boolean('is_default')) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        Address::create([
            'user_id' => $user->id,
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'landmark' => $validated['landmark'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'latitude' => $validated['latitude'] ?? session('customer_lat', 28.6315),
            'longitude' => $validated['longitude'] ?? session('customer_lng', 77.2200),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Delivery address saved successfully.');
    }

    public function deleteAddress(int $id)
    {
        $user = Auth::user();
        Address::where('user_id', $user->id)->where('id', $id)->delete();
        return back()->with('info', 'Address deleted.');
    }
}
