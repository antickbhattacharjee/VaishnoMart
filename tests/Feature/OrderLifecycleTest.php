<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryAgentEarning;
use App\Models\DeliveryAssignment;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorTransaction;
use App\Services\OrderService;
use App\Services\OtpService;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    protected OrderService $orderService;
    protected OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
        $this->otpService = app(OtpService::class);
    }

    public function test_complete_order_lifecycle_from_checkout_to_delivered_with_dual_otp_and_financial_ledger(): void
    {
        // 1. Setup Customer, Vendor, Product, and Delivery Agent
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $this->assertNotNull($customer);

        $vendorProfile = VendorProfile::where('status', 'approved')->first();
        $this->assertNotNull($vendorProfile);
        $vendorUser = $vendorProfile->user;

        $agent = User::where('role', User::ROLE_DELIVERY_AGENT)->first();
        $this->assertNotNull($agent);

        $product = Product::where('vendor_profile_id', $vendorProfile->id)
            ->where('stock_quantity', '>=', 5)
            ->first();
        $this->assertNotNull($product);

        $initialStock = $product->stock_quantity;
        $orderQty = 2;

        // 2. Setup Delivery Address
        $deliveryAddress = Address::create([
            'user_id' => $customer->id,
            'label' => 'Lifecycle Test Home',
            'recipient_name' => $customer->name,
            'recipient_phone' => $customer->phone ?? '9876543210',
            'address_line1' => 'Flat 202, Connaught Place Outer',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'latitude' => 28.6315,
            'longitude' => 77.2200,
            'is_default' => true,
        ]);

        // 3. Populate Customer Cart
        Cart::where('user_id', $customer->id)->delete();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_profile_id' => $vendorProfile->id,
            'total_amount' => $product->effective_price * $orderQty,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $orderQty,
            'unit_price' => $product->effective_price,
            'total_price' => $product->effective_price * $orderQty,
        ]);

        // 4. Place Order (Stage 1: Pending Vendor & Stock Reservation)
        $order = $this->orderService->createOrderFromCart($customer, $deliveryAddress, [
            'payment_method' => 'cod',
            'notes' => 'Please leave with security if unavailable.',
        ]);

        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING_VENDOR, $order->status);
        $this->assertEquals($vendorProfile->id, $order->vendor_profile_id);

        // Verify atomic inventory reservation
        $product->refresh();
        $this->assertEquals($initialStock - $orderQty, $product->stock_quantity);

        // 5. Vendor Accepts Order (Stage 3: Accepted & Pickup OTP Generation)
        $acceptResult = $this->orderService->vendorAccept($order, $vendorProfile);
        $this->assertTrue($acceptResult['success']);

        $order->refresh();
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->status);

        // Verify Pickup OTP was generated
        $pickupOtpRecord = OtpVerification::where('order_id', $order->id)
            ->where('stage', OtpVerification::STAGE_PICKUP)
            ->first();
        $this->assertNotNull($pickupOtpRecord);
        $this->assertFalse($pickupOtpRecord->is_verified);

        // 6. Vendor Prepares and Marks Ready for Pickup (Stage 4 & 5)
        $prepResult = $this->orderService->vendorStartPreparing($order, $vendorProfile);
        $this->assertTrue($prepResult['success']);
        $order->refresh();
        $this->assertEquals(Order::STATUS_PREPARING, $order->status);

        $readyResult = $this->orderService->vendorReadyForPickup($order, $vendorProfile);
        $this->assertTrue($readyResult['success']);
        $order->refresh();
        $this->assertContains($order->status, [Order::STATUS_READY_FOR_PICKUP, Order::STATUS_DELIVERY_REQUESTED]);

        // 7. Delivery Agent Assigned (Stage 6)
        $deliveryAssignment = DeliveryAssignment::create([
            'order_id' => $order->id,
            'delivery_agent_id' => $agent->id,
            'status' => 'accepted',
            'offered_at' => now(),
            'responded_at' => now(),
        ]);
        $order->update([
            'delivery_agent_id' => $agent->id,
            'status' => Order::STATUS_DELIVERY_ASSIGNED,
        ]);

        // 8. Pickup Verification (Stage 7: Pickup OTP Verification)
        // A) Test invalid OTP rejection
        $invalidPickup = $this->orderService->verifyPickup($order, $agent, '000000');
        $this->assertFalse($invalidPickup['success']);
        $order->refresh();
        $this->assertNotEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->status);

        // B) Generate and test valid Pickup OTP
        // Using otpService generate to get a known plaintext OTP for this test
        $generatedPickup = $this->otpService->generate($order, OtpVerification::STAGE_PICKUP, $vendorUser);
        $validPickupOtp = $generatedPickup['plain_otp'];

        $validPickupResult = $this->orderService->verifyPickup($order, $agent, $validPickupOtp);
        $this->assertTrue($validPickupResult['success']);

        $order->refresh();
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->status);

        // 9. Customer Delivery Confirmation (Stage 9: Delivery OTP Verification)
        // A) Test invalid delivery OTP rejection
        $invalidDelivery = $this->orderService->verifyDelivery($order, $agent, '999999');
        $this->assertFalse($invalidDelivery['success']);
        $order->refresh();
        $this->assertNotEquals(Order::STATUS_DELIVERED, $order->status);

        // B) Test valid Customer Delivery OTP
        $deliveryOtpRecord = OtpVerification::where('order_id', $order->id)
            ->where('stage', OtpVerification::STAGE_DELIVERY)
            ->latest('id')
            ->first();
        $this->assertNotNull($deliveryOtpRecord);

        // Generate known delivery OTP for testing
        $generatedDelivery = $this->otpService->generate($order, OtpVerification::STAGE_DELIVERY, $customer);
        $validDeliveryOtp = $generatedDelivery['plain_otp'];

        $deliveryResult = $this->orderService->verifyDelivery($order, $agent, $validDeliveryOtp);
        $this->assertTrue($deliveryResult['success']);

        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);

        // 10. Financial Ledger Verification (Section 10 & 11)
        // Verify Vendor Transaction Ledger entry exists
        $vendorTx = VendorTransaction::where('order_id', $order->id)
            ->where('vendor_profile_id', $vendorProfile->id)
            ->first();
        $this->assertNotNull($vendorTx, 'VendorTransaction ledger record must be created.');
        $this->assertEquals($order->vendor_earnings_amount, $vendorTx->amount);

        // Verify Delivery Agent Earnings record exists
        $agentEarning = DeliveryAgentEarning::where('order_id', $order->id)
            ->where('delivery_agent_id', $agent->id)
            ->first();
        $this->assertNotNull($agentEarning, 'DeliveryAgentEarning record must be created.');
        $this->assertEquals($order->delivery_agent_earnings_amount, $agentEarning->total_earning);
    }

    public function test_cancelling_order_restores_reserved_inventory(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $vendorProfile = VendorProfile::where('status', 'approved')->first();
        $product = Product::where('vendor_profile_id', $vendorProfile->id)
            ->where('stock_quantity', '>=', 10)
            ->first();
        $this->assertNotNull($product);

        $initialStock = $product->stock_quantity;
        $orderQty = 3;

        $deliveryAddress = Address::where('user_id', $customer->id)->first() ?? Address::create([
            'user_id' => $customer->id,
            'label' => 'Cancel Test Home',
            'recipient_name' => $customer->name,
            'recipient_phone' => '9876543210',
            'address_line1' => 'Connaught Place',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'latitude' => 28.6315,
            'longitude' => 77.2200,
        ]);

        Cart::where('user_id', $customer->id)->delete();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_profile_id' => $vendorProfile->id,
            'total_amount' => $product->effective_price * $orderQty,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $orderQty,
            'unit_price' => $product->effective_price,
            'total_price' => $product->effective_price * $orderQty,
        ]);

        $order = $this->orderService->createOrderFromCart($customer, $deliveryAddress, [
            'payment_method' => 'cod',
        ]);

        $product->refresh();
        $this->assertEquals($initialStock - $orderQty, $product->stock_quantity, 'Stock should be deducted after order creation.');

        // Cancel order
        $cancelResult = $this->orderService->cancelOrder($order, $customer, 'Customer changed their mind.');
        $this->assertTrue($cancelResult['success']);

        $order->refresh();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);

        // Verify stock restored
        $product->refresh();
        $this->assertEquals($initialStock, $product->stock_quantity, 'Stock must be restored to original level after cancellation.');
    }

    public function test_expired_otp_can_be_resent_and_verified_successfully(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $vendorUser = User::where('email', 'water@vaishnomart.com')->first();
        $vendorProfile = $vendorUser->vendorProfile;
        $product = Product::where('vendor_profile_id', $vendorProfile->id)->first();
        $deliveryAddress = Address::where('user_id', $customer->id)->first();
        $agent = User::where('role', User::ROLE_DELIVERY_AGENT)->first();

        // Create Order
        \App\Models\Cart::where('user_id', $customer->id)->delete();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_profile_id' => $vendorProfile->id,
            'total_amount' => $product->effective_price,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_price' => $product->effective_price,
        ]);

        $order = $this->orderService->createOrderFromCart($customer, $deliveryAddress, ['payment_method' => 'cod']);
        $this->orderService->vendorAccept($order, $vendorProfile);
        $order->update(['delivery_agent_id' => $agent->id, 'status' => Order::STATUS_DELIVERY_ASSIGNED]);

        // 1. Simulate expired pickup OTP with max attempts reached
        $pickupOtp = OtpVerification::where('order_id', $order->id)->where('stage', OtpVerification::STAGE_PICKUP)->first();
        $pickupOtp->update([
            'expires_at' => now()->subMinutes(15),
            'attempts_count' => 5,
        ]);

        // Attempt verification with expired OTP -> fails
        $failCheck = $this->orderService->verifyPickup($order, $agent, '000000');
        $this->assertFalse($failCheck['success']);

        // 2. Resend Pickup OTP as Vendor
        $this->actingAs($vendorUser);
        $resendResponse = $this->post(route('vendor.orders.resend-otp', $order->id));
        $resendResponse->assertRedirect();
        $resendResponse->assertSessionHas('success');

        // Check new OTP is active and unverified with 0 attempts
        $newPickupOtp = OtpVerification::where('order_id', $order->id)->where('stage', OtpVerification::STAGE_PICKUP)->latest('id')->first();
        $this->assertNotNull($newPickupOtp);
        $this->assertEquals(0, $newPickupOtp->attempts_count);
        $this->assertTrue(now()->lt($newPickupOtp->expires_at));

        // Verify pickup with the newly generated plain OTP
        $pickupSuccess = $this->orderService->verifyPickup($order, $agent, $newPickupOtp->plain_otp);
        $this->assertTrue($pickupSuccess['success']);
        $order->refresh();
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->status);

        // 3. Simulate expired Customer Delivery OTP
        $deliveryOtp = OtpVerification::where('order_id', $order->id)->where('stage', OtpVerification::STAGE_DELIVERY)->first();
        $deliveryOtp->update([
            'expires_at' => now()->subMinutes(20),
            'attempts_count' => 5,
        ]);

        // 4. Resend Delivery OTP as Customer
        $this->actingAs($customer);
        $resendDeliveryResponse = $this->post(route('customer.orders.resend-otp', $order->id));
        $resendDeliveryResponse->assertRedirect();
        $resendDeliveryResponse->assertSessionHas('success');

        $newDeliveryOtp = OtpVerification::where('order_id', $order->id)->where('stage', OtpVerification::STAGE_DELIVERY)->latest('id')->first();
        $this->assertNotNull($newDeliveryOtp);
        $this->assertEquals(0, $newDeliveryOtp->attempts_count);

        // Verify final delivery with new OTP
        $deliverySuccess = $this->orderService->verifyDelivery($order, $agent, $newDeliveryOtp->plain_otp);
        $this->assertTrue($deliverySuccess['success']);
        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);
    }
}
