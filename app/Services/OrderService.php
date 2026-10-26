<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\DeliveryAgentEarning;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryFeeRule;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\OtpVerification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\VendorAssignment;
use App\Models\VendorProfile;
use App\Models\VendorTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OtpService $otpService,
        protected VendorMatchingService $vendorMatchingService,
        protected DeliveryAssignmentService $deliveryService
    ) {}

    /**
     * Create an order from customer's active cart.
     */
    public function createOrderFromCart(User $customer, Address $deliveryAddress, array $data): Order
    {
        return DB::transaction(function () use ($customer, $deliveryAddress, $data) {
            $cart = Cart::with(['items.product.vendor', 'items.variant'])
                ->where('user_id', $customer->id)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception('Your shopping cart is empty.');
            }

            $vendor = $cart->vendor;
            if (!$vendor) {
                // If cart vendor is missing, pull from first product
                $vendor = $cart->items->first()->product->vendor;
            }

            if (!$vendor || !$vendor->isAvailable()) {
                throw new Exception('The selected store is currently unavailable or closed.');
            }

            // Check serviceability
            $distance = $vendor->distanceFrom((float) $deliveryAddress->latitude, (float) $deliveryAddress->longitude);
            if ($distance > (float) $vendor->service_radius_km) {
                throw new Exception("Your delivery address is outside {$vendor->business_name}'s serviceable delivery area ({$vendor->service_radius_km} km).");
            }

            // Validate inventory and calculate server-side totals
            $subtotal = 0.00;
            $depositTotal = 0.00;
            $itemsToCreate = [];

            foreach ($cart->items as $cartItem) {
                // Lock product row to prevent concurrent overselling
                $product = Product::where('id', $cartItem->product_id)->lockForUpdate()->first();

                if (!$product || !$product->is_active || !$product->is_available) {
                    throw new Exception("Product '{$cartItem->product->name}' is no longer available.");
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw new Exception("Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}.");
                }

                $unitPrice = $product->effective_price;
                if ($cartItem->variant) {
                    $unitPrice = (float) $cartItem->variant->price;
                }

                $itemSubtotal = $unitPrice * $cartItem->quantity;
                $itemDeposit = (float) $product->deposit_amount * $cartItem->quantity;

                $subtotal += $itemSubtotal;
                $depositTotal += $itemDeposit;

                // Reserve inventory immediately
                $previousStock = $product->stock_quantity;
                $newStock = $previousStock - $cartItem->quantity;
                $product->update(['stock_quantity' => $newStock]);

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $product->name,
                    'variant_name' => $cartItem->variant?->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => 0.00,
                    'deposit_amount' => $itemDeposit,
                    'total_amount' => $itemSubtotal + $itemDeposit,
                    'product_model' => $product,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                ];
            }

            // Calculate Delivery Fee
            $deliveryFee = DeliveryFeeRule::calculateFee($distance, $subtotal);

            // Calculate Coupon Discount
            $discountAmount = 0.00;
            $coupon = null;
            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', trim($data['coupon_code']))->first();
                if ($coupon) {
                    $check = $coupon->isValidForUser($customer->id, $subtotal, $vendor->id);
                    if ($check['valid']) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                    }
                }
            }

            $taxAmount = round($subtotal * 0.05, 2); // 5% standard GST
            $totalAmount = max(0.00, $subtotal + $taxAmount + $deliveryFee + $depositTotal - $discountAmount);

            // Calculate platform commission and vendor earnings
            $commissionAmount = CommissionRule::calculateCommission($subtotal, $vendor->id, $vendor->primary_category_id);
            $vendorEarnings = max(0.00, $subtotal + $depositTotal - $commissionAmount);
            $deliveryAgentEarnings = max(20.00, round(20.00 + ($distance * 6.00), 2));

            // Generate unique order number
            $orderNumber = 'VM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $paymentMethod = $data['payment_method'] ?? 'cod';
            $paymentStatus = ($paymentMethod === 'online') ? 'pending' : 'pending';
            $timeoutMinutes = (int) env('VENDOR_ACCEPTANCE_TIMEOUT_MINUTES', 5);

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'vendor_profile_id' => $vendor->id,
                'status' => Order::STATUS_PENDING_VENDOR,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'deposit_total' => $depositTotal,
                'total_amount' => $totalAmount,
                'platform_commission_amount' => $commissionAmount,
                'vendor_earnings_amount' => $vendorEarnings,
                'delivery_agent_earnings_amount' => $deliveryAgentEarnings,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'customer_notes' => $data['customer_notes'] ?? null,
                'vendor_acceptance_deadline' => Carbon::now()->addMinutes($timeoutMinutes),
            ]);

            // Save Order Items and Inventory Log
            foreach ($itemsToCreate as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'],
                    'product_name' => $itemData['product_name'],
                    'variant_name' => $itemData['variant_name'],
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['subtotal'],
                    'tax_amount' => $itemData['tax_amount'],
                    'deposit_amount' => $itemData['deposit_amount'],
                    'total_amount' => $itemData['total_amount'],
                ]);

                InventoryLog::create([
                    'product_id' => $itemData['product_id'],
                    'vendor_profile_id' => $vendor->id,
                    'quantity_change' => -$itemData['quantity'],
                    'previous_quantity' => $itemData['previous_stock'],
                    'new_quantity' => $itemData['new_stock'],
                    'type' => 'order_reserved',
                    'reference_id' => $order->order_number,
                    'notes' => "Stock reserved for Order #{$order->order_number}",
                ]);
            }

            // Save Address Snapshots
            OrderAddress::create([
                'order_id' => $order->id,
                'type' => 'delivery',
                'recipient_name' => $deliveryAddress->recipient_name,
                'phone' => $deliveryAddress->recipient_phone,
                'address_line1' => $deliveryAddress->address_line1,
                'address_line2' => $deliveryAddress->address_line2,
                'landmark' => $deliveryAddress->landmark,
                'city' => $deliveryAddress->city,
                'state' => $deliveryAddress->state,
                'postal_code' => $deliveryAddress->postal_code,
                'latitude' => $deliveryAddress->latitude,
                'longitude' => $deliveryAddress->longitude,
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'type' => 'pickup',
                'recipient_name' => $vendor->business_name,
                'phone' => $vendor->phone,
                'address_line1' => $vendor->address,
                'city' => $deliveryAddress->city ?? 'Central',
                'state' => $deliveryAddress->state ?? 'Default',
                'postal_code' => '000000',
                'latitude' => $vendor->latitude,
                'longitude' => $vendor->longitude,
            ]);

            // Save Vendor Assignment Offer
            VendorAssignment::create([
                'order_id' => $order->id,
                'vendor_profile_id' => $vendor->id,
                'status' => 'offered',
                'distance_km' => $distance,
                'rank' => 1,
                'offered_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes($timeoutMinutes),
            ]);

            // Record Coupon Redemption
            if ($coupon && $discountAmount > 0) {
                CouponRedemption::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $customer->id,
                    'order_id' => $order->id,
                    'discount_applied' => $discountAmount,
                ]);
                $coupon->increment('usage_count');
            }

            // Create Initial Payment Record
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $customer->id,
                'transaction_reference' => 'TXN-' . strtoupper(Str::random(12)),
                'payment_gateway' => $paymentMethod,
                'amount' => $totalAmount,
                'currency' => 'INR',
                'status' => ($paymentMethod === 'cod') ? 'pending' : 'pending',
            ]);

            // Record Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => null,
                'new_status' => Order::STATUS_PENDING_VENDOR,
                'actor_id' => $customer->id,
                'actor_role' => User::ROLE_CUSTOMER,
                'comment' => "Order placed by customer {$customer->name}.",
            ]);

            // Clear Cart
            $cart->items()->delete();

            // Send in-app notification to vendor
            SystemNotification::send(
                $vendor->user_id,
                'New Incoming Order',
                "New Order #{$order->order_number} received! Review and accept before the timer expires.",
                'order_update',
                "/vendor/orders/{$order->id}"
            );

            // Send confirmation to customer
            SystemNotification::send(
                $customer->id,
                'Order Placed Successfully',
                "Your order #{$order->order_number} has been sent to {$vendor->business_name} for confirmation.",
                'success',
                "/customer/orders/{$order->id}"
            );

            return $order;
        });
    }

    /**
     * Vendor accepts the incoming order.
     */
    public function vendorAccept(Order $order, VendorProfile $vendor): array
    {
        if ($order->vendor_profile_id !== $vendor->id) {
            return ['success' => false, 'message' => 'Unauthorized action for this order.'];
        }

        if ($order->status !== Order::STATUS_PENDING_VENDOR) {
            return ['success' => false, 'message' => "Order cannot be accepted from current status '{$order->status}'."];
        }

        return DB::transaction(function () use ($order, $vendor) {
            // Update assignment
            VendorAssignment::where('order_id', $order->id)
                ->where('vendor_profile_id', $vendor->id)
                ->update([
                    'status' => 'accepted',
                    'responded_at' => Carbon::now(),
                ]);

            // Transition status
            $order->update([
                'status' => Order::STATUS_ACCEPTED,
                'vendor_accepted_at' => Carbon::now(),
            ]);

            // Generate Pickup OTP (for the vendor to share with the delivery agent upon collection)
            $this->otpService->generate($order, OtpVerification::STAGE_PICKUP, $vendor->user);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_PENDING_VENDOR,
                'new_status' => Order::STATUS_ACCEPTED,
                'actor_id' => $vendor->user_id,
                'actor_role' => User::ROLE_VENDOR,
                'comment' => "Order accepted by store {$vendor->business_name}.",
            ]);

            SystemNotification::send(
                $order->customer_id,
                'Order Accepted!',
                "{$vendor->business_name} has accepted your order #{$order->order_number} and will begin preparing it shortly.",
                'order_update',
                "/customer/orders/{$order->id}"
            );

            return ['success' => true, 'message' => 'Order accepted successfully.'];
        });
    }

    /**
     * Vendor rejects the order and triggers automatic reassignment to the next eligible vendor.
     */
    public function vendorReject(Order $order, VendorProfile $vendor, string $reason): array
    {
        if ($order->vendor_profile_id !== $vendor->id) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if ($order->status !== Order::STATUS_PENDING_VENDOR) {
            return ['success' => false, 'message' => 'Order is not in pending vendor status.'];
        }

        return DB::transaction(function () use ($order, $vendor, $reason) {
            VendorAssignment::where('order_id', $order->id)
                ->where('vendor_profile_id', $vendor->id)
                ->update([
                    'status' => 'rejected',
                    'responded_at' => Carbon::now(),
                    'rejection_reason' => $reason,
                ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_PENDING_VENDOR,
                'new_status' => Order::STATUS_REJECTED,
                'actor_id' => $vendor->user_id,
                'actor_role' => User::ROLE_VENDOR,
                'comment' => "Vendor rejected order: {$reason}",
            ]);

            // Attempt assignment to next nearest vendor
            $nextAssignment = $this->vendorMatchingService->assignNextVendor($order);

            if ($nextAssignment) {
                $newVendor = $nextAssignment->vendor;
                SystemNotification::send(
                    $newVendor->user_id,
                    'New Order Re-routed to Your Store',
                    "Order #{$order->order_number} was routed to your store. Please review and accept.",
                    'order_update',
                    "/vendor/orders/{$order->id}"
                );

                return ['success' => true, 'message' => 'Order rejected. System has re-routed the order to the next available store.'];
            }

            // No other vendor can fulfill, cancel and restore inventory
            $this->cancelOrderInternal($order, null, 'No alternative nearby vendor available after rejection.');

            return ['success' => true, 'message' => 'Order rejected. No alternative vendors were available, order cancelled.'];
        });
    }

    /**
     * Vendor marks order as preparing.
     */
    public function vendorStartPreparing(Order $order, VendorProfile $vendor): array
    {
        if ($order->vendor_profile_id !== $vendor->id) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if ($order->status !== Order::STATUS_ACCEPTED) {
            return ['success' => false, 'message' => 'Invalid order status.'];
        }

        $order->update([
            'status' => Order::STATUS_PREPARING,
            'prepared_at' => Carbon::now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'previous_status' => Order::STATUS_ACCEPTED,
            'new_status' => Order::STATUS_PREPARING,
            'actor_id' => $vendor->user_id,
            'actor_role' => User::ROLE_VENDOR,
            'comment' => 'Store is preparing the order.',
        ]);

        SystemNotification::send(
            $order->customer_id,
            'Order is Being Prepared',
            "{$vendor->business_name} has started preparing your order.",
            'order_update',
            "/customer/orders/{$order->id}"
        );

        return ['success' => true, 'message' => 'Order is marked as preparing.'];
    }

    /**
     * Vendor marks order as ready for pickup and requests delivery agents.
     */
    public function vendorReadyForPickup(Order $order, VendorProfile $vendor): array
    {
        if ($order->vendor_profile_id !== $vendor->id) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if (!in_array($order->status, [Order::STATUS_ACCEPTED, Order::STATUS_PREPARING], true)) {
            return ['success' => false, 'message' => 'Invalid order status.'];
        }

        return DB::transaction(function () use ($order, $vendor) {
            $order->update([
                'status' => Order::STATUS_READY_FOR_PICKUP,
                'prepared_at' => Carbon::now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_PREPARING,
                'new_status' => Order::STATUS_READY_FOR_PICKUP,
                'actor_id' => $vendor->user_id,
                'actor_role' => User::ROLE_VENDOR,
                'comment' => 'Order is packed and ready for pickup.',
            ]);

            // Broadcast delivery requests to nearby agents
            $offeredAgents = $this->deliveryService->requestDeliveryAgents($order);

            SystemNotification::send(
                $order->customer_id,
                'Order Packed & Ready',
                "Your order #{$order->order_number} is packed. We are finding a nearby delivery partner.",
                'order_update',
                "/customer/orders/{$order->id}"
            );

            return [
                'success' => true,
                'message' => "Order marked ready for pickup! Delivery requests broadcasted to {$offeredAgents} available delivery partners.",
            ];
        });
    }

    /**
     * Delivery Agent verifies Pickup OTP from Vendor.
     * Crucial security rule: backend verification is mandatory!
     */
    public function verifyPickup(Order $order, User $agent, string $plainOtp): array
    {
        if ($order->delivery_agent_id !== $agent->id) {
            return ['success' => false, 'message' => 'You are not the assigned delivery partner for this order.'];
        }

        if (!in_array($order->status, [Order::STATUS_DELIVERY_ASSIGNED, Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERY_REQUESTED], true)) {
            return ['success' => false, 'message' => "Order cannot be picked up from status '{$order->status}'."];
        }

        // Validate Pickup OTP on the server
        $otpCheck = $this->otpService->verify($order, OtpVerification::STAGE_PICKUP, $plainOtp);
        if (!$otpCheck['success']) {
            return $otpCheck;
        }

        return DB::transaction(function () use ($order, $agent) {
            // Update DeliveryAssignment timestamp
            DeliveryAssignment::where('order_id', $order->id)
                ->where('delivery_agent_id', $agent->id)
                ->update(['pickup_verified_at' => Carbon::now()]);

            // Move to picked_up, then out_for_delivery
            $order->update([
                'status' => Order::STATUS_OUT_FOR_DELIVERY,
                'picked_up_at' => Carbon::now(),
                'out_for_delivery_at' => Carbon::now(),
            ]);

            // Generate Customer Delivery Confirmation OTP
            $deliveryOtp = $this->otpService->generate($order, OtpVerification::STAGE_DELIVERY, $order->customer);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_DELIVERY_ASSIGNED,
                'new_status' => Order::STATUS_OUT_FOR_DELIVERY,
                'actor_id' => $agent->id,
                'actor_role' => User::ROLE_DELIVERY_AGENT,
                'comment' => "Pickup verified with store OTP. Order is now out for delivery.",
            ]);

            // Notify customer with their delivery confirmation OTP
            SystemNotification::send(
                $order->customer_id,
                'Order Out for Delivery! 🚀',
                "Your order #{$order->order_number} has been picked up by {$agent->name}. Share OTP {$deliveryOtp['plain_otp']} upon delivery.",
                'order_update',
                "/customer/orders/{$order->id}"
            );

            return [
                'success' => true,
                'message' => 'Pickup verified successfully! Order is now out for delivery.',
            ];
        });
    }

    /**
     * Delivery Agent verifies Delivery OTP from Customer.
     * Completes the order and records all financial ledger entries.
     */
    public function verifyDelivery(Order $order, User $agent, string $plainOtp): array
    {
        if ($order->delivery_agent_id !== $agent->id) {
            return ['success' => false, 'message' => 'You are not the assigned delivery partner for this order.'];
        }

        if ($order->status !== Order::STATUS_OUT_FOR_DELIVERY) {
            return ['success' => false, 'message' => "Order cannot be completed from status '{$order->status}'."];
        }

        // Validate Delivery OTP on the server
        $otpCheck = $this->otpService->verify($order, OtpVerification::STAGE_DELIVERY, $plainOtp);
        if (!$otpCheck['success']) {
            return $otpCheck;
        }

        return DB::transaction(function () use ($order, $agent) {
            // Update DeliveryAssignment
            DeliveryAssignment::where('order_id', $order->id)
                ->where('delivery_agent_id', $agent->id)
                ->update([
                    'status' => 'completed',
                    'delivery_verified_at' => Carbon::now(),
                ]);

            // Mark order delivered
            $order->update([
                'status' => Order::STATUS_DELIVERED,
                'delivered_at' => Carbon::now(),
                'payment_status' => 'paid',
            ]);

            // Finalize payment record
            Payment::where('order_id', $order->id)
                ->update([
                    'status' => 'successful',
                    'verified_at' => Carbon::now(),
                ]);

            // 1. Credit Vendor Financial Ledger
            $vendor = $order->vendor;
            $prevBalance = $vendor->wallet_balance;
            $creditAmount = (float) $order->vendor_earnings_amount;
            $newBalance = $prevBalance + $creditAmount;

            VendorTransaction::create([
                'vendor_profile_id' => $vendor->id,
                'order_id' => $order->id,
                'type' => VendorTransaction::TYPE_ORDER_CREDIT,
                'amount' => $creditAmount,
                'balance_after' => $newBalance,
                'description' => "Order earnings for #{$order->order_number} (Subtotal: ₹{$order->subtotal} - Commission: ₹{$order->platform_commission_amount})",
                'reference_number' => 'VTR-' . strtoupper(Str::random(10)),
            ]);

            // 2. Record Delivery Agent Earnings
            DeliveryAgentEarning::create([
                'delivery_agent_id' => $agent->id,
                'order_id' => $order->id,
                'base_earning' => 20.00,
                'distance_incentive' => max(0.00, $order->delivery_agent_earnings_amount - 20.00),
                'tip_amount' => 0.00,
                'total_earning' => $order->delivery_agent_earnings_amount,
                'status' => 'pending',
            ]);

            // Release delivery agent back to available
            if ($agent->deliveryAgentProfile) {
                $agent->deliveryAgentProfile->update(['is_available' => true]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_OUT_FOR_DELIVERY,
                'new_status' => Order::STATUS_DELIVERED,
                'actor_id' => $agent->id,
                'actor_role' => User::ROLE_DELIVERY_AGENT,
                'comment' => "Delivery confirmed via customer OTP by agent {$agent->name}.",
            ]);

            // Notify customer
            SystemNotification::send(
                $order->customer_id,
                'Order Delivered Successfully! 🎉',
                "Your order #{$order->order_number} has been delivered. Please share your rating and review!",
                'success',
                "/customer/orders/{$order->id}"
            );

            // Notify vendor
            SystemNotification::send(
                $vendor->user_id,
                'Order Delivered & Settled',
                "Order #{$order->order_number} has been delivered. ₹{$creditAmount} has been credited to your store ledger.",
                'success',
                "/vendor/orders/{$order->id}"
            );

            return [
                'success' => true,
                'message' => 'Delivery verified successfully! Order completed and earnings credited.',
            ];
        });
    }

    /**
     * Regenerate / Resend Pickup OTP for store handover (Vendor or Agent).
     */
    public function resendPickupOtp(Order $order, User $actor): array
    {
        $validStatuses = [
            Order::STATUS_ACCEPTED,
            Order::STATUS_PREPARING,
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_DELIVERY_REQUESTED,
            Order::STATUS_DELIVERY_ASSIGNED,
        ];

        if (!in_array($order->status, $validStatuses, true)) {
            return [
                'success' => false,
                'message' => "Pickup OTP cannot be regenerated for an order with status '{$order->status}'.",
            ];
        }

        $isVendor = $order->vendor && $order->vendor->user_id === $actor->id;
        $isAgent = $order->delivery_agent_id === $actor->id;
        $isAdmin = in_array($actor->role, [User::ROLE_SUPER_ADMIN, 'admin'], true);

        if (!$isVendor && !$isAgent && !$isAdmin) {
            return [
                'success' => false,
                'message' => 'Unauthorized to regenerate pickup OTP for this order.',
            ];
        }

        $newOtp = $this->otpService->generate($order, OtpVerification::STAGE_PICKUP, $order->vendor?->user);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'previous_status' => $order->status,
            'new_status' => $order->status,
            'actor_id' => $actor->id,
            'actor_role' => $actor->role,
            'comment' => "Pickup OTP regenerated by {$actor->name}.",
        ]);

        return [
            'success' => true,
            'message' => 'New Store Pickup OTP generated successfully.',
            'plain_otp' => $newOtp['plain_otp'],
        ];
    }

    /**
     * Regenerate / Resend Customer Delivery Confirmation OTP (Customer or Agent).
     */
    public function resendDeliveryOtp(Order $order, User $actor): array
    {
        if ($order->status !== Order::STATUS_OUT_FOR_DELIVERY) {
            return [
                'success' => false,
                'message' => "Delivery confirmation OTP can only be resent when order is out for delivery.",
            ];
        }

        $isCustomer = $order->customer_id === $actor->id;
        $isAgent = $order->delivery_agent_id === $actor->id;
        $isAdmin = in_array($actor->role, [User::ROLE_SUPER_ADMIN, 'admin'], true);

        if (!$isCustomer && !$isAgent && !$isAdmin) {
            return [
                'success' => false,
                'message' => 'Unauthorized to resend delivery OTP for this order.',
            ];
        }

        $newOtp = $this->otpService->generate($order, OtpVerification::STAGE_DELIVERY, $order->customer);

        SystemNotification::send(
            $order->customer_id,
            'New Delivery Confirmation OTP 🚀',
            "Your new delivery verification OTP for order #{$order->order_number} is {$newOtp['plain_otp']}. Share with rider upon arrival.",
            'order_update',
            "/customer/orders/{$order->id}"
        );

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'previous_status' => $order->status,
            'new_status' => $order->status,
            'actor_id' => $actor->id,
            'actor_role' => $actor->role,
            'comment' => "Delivery confirmation OTP regenerated by {$actor->name}.",
        ]);

        return [
            'success' => true,
            'message' => 'New Delivery Confirmation OTP generated and sent to customer.',
            'plain_otp' => $newOtp['plain_otp'],
        ];
    }

    /**
     * Cancel an order and restore reserved stock.
     */
    public function cancelOrder(Order $order, User $actor, string $reason): array
    {
        // Allowed cancellation statuses: pending_vendor, accepted, preparing
        $cancellableStatuses = [
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PENDING_VENDOR,
            Order::STATUS_ACCEPTED,
        ];

        if (!in_array($order->status, $cancellableStatuses, true) && !$actor->isAdmin()) {
            return [
                'success' => false,
                'message' => "Order cannot be cancelled at status '{$order->status}'. Please contact customer support.",
            ];
        }

        $this->cancelOrderInternal($order, $actor, $reason);

        return [
            'success' => true,
            'message' => 'Order has been successfully cancelled and inventory restored.',
        ];
    }

    protected function cancelOrderInternal(Order $order, ?User $actor, string $reason): void
    {
        DB::transaction(function () use ($order, $actor, $reason) {
            // Restore inventory for all items
            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $prev = $product->stock_quantity;
                    $restored = $prev + $item->quantity;
                    $product->update(['stock_quantity' => $restored]);

                    InventoryLog::create([
                        'product_id' => $product->id,
                        'vendor_profile_id' => $order->vendor_profile_id,
                        'quantity_change' => $item->quantity,
                        'previous_quantity' => $prev,
                        'new_quantity' => $restored,
                        'type' => 'order_released',
                        'reference_id' => $order->order_number,
                        'notes' => "Stock restored due to order cancellation: {$reason}",
                    ]);
                }
            }

            // Invalidate pending OTPs
            $this->otpService->invalidate($order, OtpVerification::STAGE_PICKUP);
            $this->otpService->invalidate($order, OtpVerification::STAGE_DELIVERY);

            // If online payment was completed, process refund entry
            if ($order->payment_status === 'paid') {
                Refund::create([
                    'order_id' => $order->id,
                    'payment_id' => $order->latestPayment?->id,
                    'refund_reference' => 'REF-' . strtoupper(Str::random(10)),
                    'amount' => $order->total_amount,
                    'reason' => $reason,
                    'status' => 'pending',
                ]);
                $order->update(['status' => Order::STATUS_REFUND_PENDING]);
            } else {
                $order->update(['status' => Order::STATUS_CANCELLED]);
            }

            $order->update([
                'cancellation_reason' => $reason,
                'cancelled_at' => Carbon::now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'previous_status' => $order->status,
                'new_status' => Order::STATUS_CANCELLED,
                'actor_id' => $actor?->id,
                'actor_role' => $actor?->role ?? 'system',
                'comment' => "Order cancelled: {$reason}",
            ]);

            SystemNotification::send(
                $order->customer_id,
                'Order Cancelled',
                "Order #{$order->order_number} was cancelled. Reason: {$reason}",
                'warning',
                "/customer/orders/{$order->id}"
            );
        });
    }
}
