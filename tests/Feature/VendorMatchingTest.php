<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorAssignment;
use App\Models\VendorProfile;
use App\Services\VendorMatchingService;
use Carbon\Carbon;
use Tests\TestCase;

class VendorMatchingTest extends TestCase
{
    protected VendorMatchingService $matchingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchingService = app(VendorMatchingService::class);
    }

    public function test_nearest_eligible_vendor_is_ranked_first(): void
    {
        // Customer location at Connaught Place Central (28.6315, 77.2200)
        $customerLat = 28.6315;
        $customerLng = 77.2200;

        $category = Category::first();
        $this->assertNotNull($category);

        $vendorUser1 = User::create([
            'name' => 'Near Store Owner',
            'email' => 'near_' . rand(1000, 9999) . '@test.com',
            'phone' => '91' . rand(10000000, 99999999),
            'role' => User::ROLE_VENDOR,
            'password' => bcrypt('password123'),
        ]);

        $nearVendor = VendorProfile::create([
            'user_id' => $vendorUser1->id,
            'primary_category_id' => $category->id,
            'business_name' => 'Near Fast Mart',
            'owner_name' => 'Near Owner',
            'phone' => $vendorUser1->phone,
            'address' => '0.5 km away, CP',
            'latitude' => 28.6320,
            'longitude' => 77.2210, // ~0.1 km away
            'service_radius_km' => 5.0,
            'is_open' => true,
            'is_online' => true,
            'is_accepting_orders' => true,
            'status' => 'approved',
            'prep_time_minutes' => 10,
        ]);

        $productNear = Product::create([
            'vendor_profile_id' => $nearVendor->id,
            'category_id' => $category->id,
            'name' => 'Test Water Can',
            'slug' => 'test-water-can-near-' . rand(100, 999),
            'base_price' => 90.00,
            'stock_quantity' => 20,
            'is_active' => true,
            'is_available' => true,
        ]);

        $ranked = $this->matchingService->findRankedVendors(
            $customerLat,
            $customerLng,
            [['product_id' => $productNear->id, 'quantity' => 2]],
            $nearVendor->id
        );

        $this->assertNotEmpty($ranked);
        $this->assertEquals($nearVendor->id, $ranked[0]['vendor']->id);
        $this->assertLessThan(1.0, $ranked[0]['distance_km']);
    }

    public function test_vendor_outside_service_radius_is_excluded(): void
    {
        $customerLat = 28.6315;
        $customerLng = 77.2200;
        $category = Category::first();

        $vendorUser = User::create([
            'name' => 'Far Store Owner',
            'email' => 'far_' . rand(1000, 9999) . '@test.com',
            'phone' => '92' . rand(10000000, 99999999),
            'role' => User::ROLE_VENDOR,
            'password' => bcrypt('password123'),
        ]);

        $farVendor = VendorProfile::create([
            'user_id' => $vendorUser->id,
            'primary_category_id' => $category->id,
            'business_name' => 'Far Suburban Mart',
            'owner_name' => 'Far Owner',
            'phone' => $vendorUser->phone,
            'address' => 'Noida Sector 62',
            'latitude' => 28.6280,
            'longitude' => 77.3700, // ~15km away
            'service_radius_km' => 3.0, // only serves 3km radius
            'is_open' => true,
            'is_online' => true,
            'is_accepting_orders' => true,
            'status' => 'approved',
            'prep_time_minutes' => 15,
        ]);

        $productFar = Product::create([
            'vendor_profile_id' => $farVendor->id,
            'category_id' => $category->id,
            'name' => 'Suburban Organic Tea',
            'slug' => 'suburban-tea-' . rand(100, 999),
            'base_price' => 120.00,
            'stock_quantity' => 50,
            'is_active' => true,
            'is_available' => true,
        ]);

        $ranked = $this->matchingService->findRankedVendors(
            $customerLat,
            $customerLng,
            [['product_id' => $productFar->id, 'quantity' => 1]],
            $farVendor->id
        );

        $this->assertEmpty($ranked, 'Far vendor with small service radius must be excluded.');
    }

    public function test_vendor_with_insufficient_stock_is_excluded(): void
    {
        $customerLat = 28.6315;
        $customerLng = 77.2200;
        $category = Category::first();

        $vendorUser = User::create([
            'name' => 'Low Stock Owner',
            'email' => 'lowstock_' . rand(1000, 9999) . '@test.com',
            'phone' => '93' . rand(10000000, 99999999),
            'role' => User::ROLE_VENDOR,
            'password' => bcrypt('password123'),
        ]);

        $vendor = VendorProfile::create([
            'user_id' => $vendorUser->id,
            'primary_category_id' => $category->id,
            'business_name' => 'Low Stock Corner',
            'owner_name' => 'Low Stock Owner',
            'phone' => $vendorUser->phone,
            'address' => 'Connaught Circle',
            'latitude' => 28.6318,
            'longitude' => 77.2205,
            'service_radius_km' => 5.0,
            'is_open' => true,
            'is_online' => true,
            'is_accepting_orders' => true,
            'status' => 'approved',
            'prep_time_minutes' => 10,
        ]);

        $product = Product::create([
            'vendor_profile_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Limited Edition Butter',
            'slug' => 'limited-butter-' . rand(100, 999),
            'base_price' => 150.00,
            'stock_quantity' => 1, // Only 1 in stock
            'is_active' => true,
            'is_available' => true,
        ]);

        // Customer requests 5 units, but stock is only 1
        $ranked = $this->matchingService->findRankedVendors(
            $customerLat,
            $customerLng,
            [['product_id' => $product->id, 'quantity' => 5]],
            $vendor->id
        );

        $this->assertEmpty($ranked, 'Vendor with insufficient inventory must be excluded.');
    }

    public function test_expired_vendor_offer_is_marked_expired(): void
    {
        $vendor = VendorProfile::where('status', 'approved')->first();
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $this->assertNotNull($vendor);
        $this->assertNotNull($customer);

        $order = Order::create([
            'customer_id' => $customer->id,
            'vendor_profile_id' => $vendor->id,
            'order_number' => 'VM-TEST-' . rand(10000, 99999),
            'status' => Order::STATUS_PENDING_VENDOR,
            'subtotal' => 200,
            'delivery_fee' => 30,
            'tax_amount' => 10,
            'total_amount' => 240,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        $assignment = VendorAssignment::create([
            'order_id' => $order->id,
            'vendor_profile_id' => $vendor->id,
            'status' => 'offered',
            'distance_km' => 1.2,
            'rank' => 1,
            'offered_at' => Carbon::now()->subMinutes(10),
            'expires_at' => Carbon::now()->subMinutes(5), // expired 5 mins ago
        ]);

        $this->matchingService->processExpiredVendorOffers();

        $assignment->refresh();
        $this->assertEquals('expired', $assignment->status);
    }
}
