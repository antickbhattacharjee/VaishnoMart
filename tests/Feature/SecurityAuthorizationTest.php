<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customer1 = User::where('role', User::ROLE_CUSTOMER)->first();
        $customer2 = User::create([
            'name' => 'Intruder Customer',
            'email' => 'intruder_' . rand(1000, 9999) . '@test.com',
            'phone' => '94' . rand(10000000, 99999999),
            'role' => User::ROLE_CUSTOMER,
            'password' => bcrypt('password123'),
        ]);

        $order = Order::where('customer_id', $customer1->id)->first();
        if ($order) {
            // Acting as customer2 trying to view customer1's order
            $response = $this->actingAs($customer2)->get("/customer/orders/{$order->id}");
            $response->assertStatus(404); // Scoped to customer_id, so returns 404
        }
    }

    public function test_vendor_cannot_modify_another_vendors_product(): void
    {
        $vendors = VendorProfile::with('user')->where('status', 'approved')->get();
        if ($vendors->count() >= 2) {
            $vendor1 = $vendors[0];
            $vendor2 = $vendors[1];

            $product1 = Product::where('vendor_profile_id', $vendor1->id)->first();
            if ($product1) {
                // Vendor 2 attempts to edit product belonging to Vendor 1
                $response = $this->actingAs($vendor2->user)->get("/vendor/products/{$product1->id}/edit");
                $response->assertStatus(404); // Scoped to vendor_profile_id, so returns 404

                // Vendor 2 attempts to update product belonging to Vendor 1
                $updateResponse = $this->actingAs($vendor2->user)->post("/vendor/products/{$product1->id}/update", [
                    'name' => 'Malicious Product Title Hack',
                    'base_price' => 1.00,
                    'stock_quantity' => 10,
                ]);
                $updateResponse->assertStatus(404);

                $product1->refresh();
                $this->assertNotEquals('Malicious Product Title Hack', $product1->name);
            }
        }
    }

    public function test_vendor_cannot_view_or_accept_another_vendors_incoming_order(): void
    {
        $vendors = VendorProfile::with('user')->where('status', 'approved')->get();
        if ($vendors->count() >= 2) {
            $vendor1 = $vendors[0];
            $vendor2 = $vendors[1];

            $order = Order::where('vendor_profile_id', $vendor1->id)->first();
            if ($order) {
                // Vendor 2 attempts to view Vendor 1's order details
                $response = $this->actingAs($vendor2->user)->get("/vendor/orders/{$order->id}");
                $response->assertStatus(404);

                // Vendor 2 attempts to accept Vendor 1's order
                $acceptResponse = $this->actingAs($vendor2->user)->post("/vendor/orders/{$order->id}/accept");
                $acceptResponse->assertStatus(404);
            }
        }
    }

    public function test_non_admin_cannot_access_platform_settings_or_audit_logs(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $vendor = User::where('role', User::ROLE_VENDOR)->first();
        $agent = User::where('role', User::ROLE_DELIVERY_AGENT)->first();

        // Customer
        $this->actingAs($customer)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($customer)->get('/admin/audit-logs')->assertStatus(403);

        // Vendor
        $this->actingAs($vendor)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($vendor)->get('/admin/audit-logs')->assertStatus(403);

        // Agent
        $this->actingAs($agent)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($agent)->get('/admin/audit-logs')->assertStatus(403);
    }

    public function test_enterprise_security_headers_are_enforced(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }

    public function test_failed_login_creates_audit_log_entry(): void
    {
        $randomEmail = 'unknown_' . rand(1000, 9999) . '@test.com';

        $this->post('/login', [
            'login' => $randomEmail,
            'password' => 'wrongpassword123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'failed_login_attempt',
            'entity_type' => 'User',
        ]);
    }
}
