<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_customer_can_register_successfully(): void
    {
        $uniquePhone = '98' . rand(10000000, 99999999);
        $uniqueEmail = 'cust_' . rand(1000, 9999) . '@test.com';

        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => User::ROLE_CUSTOMER,
        ]);
        $this->assertAuthenticated();
    }

    public function test_vendor_can_register_with_store_profile(): void
    {
        $category = Category::first() ?? Category::create([
            'name' => 'General Essentials',
            'slug' => 'general-essentials',
            'is_active' => true,
        ]);

        $uniquePhone = '97' . rand(10000000, 99999999);
        $uniqueEmail = 'vendor_' . rand(1000, 9999) . '@test.com';

        $response = $this->post('/vendor-register', [
            'owner_name' => 'Store Owner',
            'business_name' => 'Green Valley Organics',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'primary_category_id' => $category->id,
            'address' => 'Connaught Place Outer Circle, New Delhi',
            'service_radius_km' => 6.5,
            'latitude' => 28.6328,
            'longitude' => 77.2197,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => User::ROLE_VENDOR,
        ]);
        $this->assertDatabaseHas('vendor_profiles', [
            'business_name' => 'Green Valley Organics',
        ]);
    }

    public function test_delivery_agent_can_register_with_vehicle_details(): void
    {
        $uniquePhone = '96' . rand(10000000, 99999999);
        $uniqueEmail = 'agent_' . rand(1000, 9999) . '@test.com';

        $response = $this->post('/delivery-register', [
            'name' => 'Fleet Rider',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'vehicle_type' => 'bike',
            'vehicle_number' => 'DL 01 AB 1234',
            'license_number' => 'DL-987654321',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('agent.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => User::ROLE_DELIVERY_AGENT,
        ]);
        $this->assertDatabaseHas('delivery_agent_profiles', [
            'vehicle_type' => 'bike',
        ]);
    }

    public function test_user_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@vaishnomart.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_customer_is_redirected_to_home_upon_login(): void
    {
        $response = $this->post('/login', [
            'login' => 'customer@vaishnomart.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
    }

    public function test_unauthorized_user_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        if ($customer) {
            $response = $this->actingAs($customer)->get('/admin/dashboard');
            $response->assertStatus(403);
        }
    }

    public function test_guest_cannot_access_vendor_dashboard(): void
    {
        $response = $this->get('/vendor/dashboard');
        $response->assertRedirect(route('login'));
    }
}
