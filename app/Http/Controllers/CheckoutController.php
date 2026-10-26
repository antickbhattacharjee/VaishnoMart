<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\DeliveryFeeRule;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $cart = Cart::with(['items.product.vendor', 'items.variant'])
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add items before checking out.');
        }

        $addresses = Address::where('user_id', $user->id)->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $vendor = $cart->vendor;
        $subtotal = $cart->subtotal;

        $distance = 2.5; // fallback
        if ($defaultAddress && $vendor) {
            $distance = $vendor->distanceFrom((float) $defaultAddress->latitude, (float) $defaultAddress->longitude);
        }

        $deliveryFee = $vendor ? DeliveryFeeRule::calculateFee($distance, $subtotal) : 25.00;
        $taxAmount = round($subtotal * 0.05, 2);
        $depositTotal = $cart->items->sum(function ($item) {
            return (float) ($item->product->deposit_amount ?? 0) * $item->quantity;
        });

        $totalAmount = $subtotal + $taxAmount + $deliveryFee + $depositTotal;

        return view('checkout.index', compact(
            'cart',
            'addresses',
            'defaultAddress',
            'vendor',
            'subtotal',
            'deliveryFee',
            'taxAmount',
            'depositTotal',
            'totalAmount',
            'distance'
        ));
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $cart = Cart::with(['items.product'])->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 400);
        }

        $coupon = Coupon::where('code', trim($validated['coupon_code']))->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 404);
        }

        $check = $coupon->isValidForUser($user->id, $cart->subtotal, $cart->vendor_profile_id);
        if (!$check['valid']) {
            return response()->json(['success' => false, 'message' => $check['message']], 422);
        }

        $discount = $coupon->calculateDiscount($cart->subtotal);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$coupon->code}' applied successfully!",
            'discount' => $discount,
            'coupon_code' => $coupon->code,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'address_id' => ['nullable', 'exists:addresses,id'],
            'new_address_line1' => ['nullable', 'string', 'max:255'],
            'new_city' => ['nullable', 'string', 'max:100'],
            'new_postal_code' => ['nullable', 'string', 'max:10'],
            'new_phone' => ['nullable', 'string', 'max:15'],
            'payment_method' => ['required', 'in:cod,online,upi'],
            'coupon_code' => ['nullable', 'string'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        // Resolve address
        if (!empty($validated['address_id'])) {
            $address = Address::where('user_id', $user->id)->findOrFail($validated['address_id']);
        } else {
            if (empty($validated['new_address_line1'])) {
                return back()->withErrors(['address_id' => 'Please select an existing delivery address or enter a new one.']);
            }

            $address = Address::create([
                'user_id' => $user->id,
                'label' => 'Delivery Location',
                'recipient_name' => $user->name,
                'recipient_phone' => $validated['new_phone'] ?? $user->phone ?? '9876543210',
                'address_line1' => $validated['new_address_line1'],
                'city' => $validated['new_city'] ?? 'Central City',
                'state' => 'Delhi',
                'postal_code' => $validated['new_postal_code'] ?? '110001',
                'latitude' => (float) session('customer_lat', 28.6315),
                'longitude' => (float) session('customer_lng', 77.2200),
                'is_default' => false,
            ]);
        }

        try {
            $order = $this->orderService->createOrderFromCart($user, $address, $validated);

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', "Order #{$order->order_number} placed successfully! Tracking is now active.");
        } catch (Exception $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
