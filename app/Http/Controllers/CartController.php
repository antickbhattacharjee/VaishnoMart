<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryFeeRule;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get or create active cart for user or guest session.
     */
    protected function getActiveCart(): Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            $cart = Cart::with(['items.product.vendor', 'items.variant'])
                ->firstOrCreate(['user_id' => $userId]);

            // Merge guest cart if exists
            $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($guestCart && $guestCart->id !== $cart->id) {
                if ($cart->items->isEmpty()) {
                    $cart->vendor_profile_id = $guestCart->vendor_profile_id;
                    $cart->save();
                    $guestCart->items()->update(['cart_id' => $cart->id]);
                }
                $guestCart->delete();
            }
        } else {
            $cart = Cart::with(['items.product.vendor', 'items.variant'])
                ->firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart;
    }

    public function index()
    {
        $cart = $this->getActiveCart();
        $items = $cart->items;
        $vendor = $cart->vendor;

        $userLat = (float) session('customer_lat', 28.6315);
        $userLng = (float) session('customer_lng', 77.2200);

        $distance = 0.0;
        if ($vendor) {
            $distance = $vendor->distanceFrom($userLat, $userLng);
        }

        $subtotal = $cart->subtotal;
        $deliveryFee = $vendor ? DeliveryFeeRule::calculateFee($distance, $subtotal) : 0.00;
        $taxAmount = round($subtotal * 0.05, 2);
        $depositTotal = $items->sum(function ($item) {
            return (float) ($item->product->deposit_amount ?? 0) * $item->quantity;
        });

        $estimatedTotal = $subtotal + $taxAmount + $deliveryFee + $depositTotal;

        return view('cart.index', compact(
            'cart',
            'items',
            'vendor',
            'subtotal',
            'deliveryFee',
            'taxAmount',
            'depositTotal',
            'estimatedTotal',
            'distance'
        ));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'confirm_vendor_switch' => ['nullable', 'boolean'],
        ]);

        $product = Product::with('vendor')->findOrFail($validated['product_id']);
        $cart = $this->getActiveCart();

        // Check if cart already has items from another vendor (Single-Vendor Rule)
        if ($cart->items->isNotEmpty() && $cart->vendor_profile_id !== null && $cart->vendor_profile_id !== $product->vendor_profile_id) {
            if (!$request->boolean('confirm_vendor_switch')) {
                $currentVendor = $cart->vendor->business_name ?? 'another store';
                $newVendor = $product->vendor->business_name ?? 'this store';

                return response()->json([
                    'requires_confirmation' => true,
                    'current_vendor' => $currentVendor,
                    'new_vendor' => $newVendor,
                    'message' => "Your cart currently contains items from '{$currentVendor}'. VaishnoMart orders are fulfilled by a single store. Would you like to clear your cart and start a fresh order from '{$newVendor}'?",
                ], 409);
            }

            // User confirmed switch: clear previous store items
            $cart->items()->delete();
        }

        // Set cart's store
        $cart->vendor_profile_id = $product->vendor_profile_id;
        $cart->save();

        $variant = null;
        $unitPrice = $product->effective_price;
        if (!empty($validated['variant_id'])) {
            $variant = ProductVariant::find($validated['variant_id']);
            if ($variant) {
                $unitPrice = (float) $variant->price;
            }
        }

        // Find if item already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$product->name} added to cart!",
                'cart_count' => $cart->itemsCount,
            ]);
        }

        return redirect()->route('cart.index')->with('success', "{$product->name} added to cart!");
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = $this->getActiveCart();
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($id);

        if ($item->product->stock_quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Only {$item->product->stock_quantity} units available in stock."]);
        }

        $item->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove(int $id)
    {
        $cart = $this->getActiveCart();
        CartItem::where('cart_id', $cart->id)->where('id', $id)->delete();

        if ($cart->items()->count() === 0) {
            $cart->update(['vendor_profile_id' => null]);
        }

        return back()->with('info', 'Item removed from cart.');
    }

    public function clear()
    {
        $cart = $this->getActiveCart();
        $cart->items()->delete();
        $cart->update(['vendor_profile_id' => null]);

        return redirect()->route('home')->with('info', 'Your cart has been cleared.');
    }
}
