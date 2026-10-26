<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryAgentProfile;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    public function test_database_is_seeded_with_core_records(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@vaishnomart.com', 'role' => User::ROLE_SUPER_ADMIN]);
        $this->assertDatabaseHas('users', ['email' => 'water@vaishnomart.com', 'role' => User::ROLE_VENDOR]);
        $this->assertDatabaseHas('users', ['email' => 'rohan@vaishnomart.com', 'role' => User::ROLE_DELIVERY_AGENT]);
        $this->assertDatabaseHas('users', ['email' => 'customer@vaishnomart.com', 'role' => User::ROLE_CUSTOMER]);

        $this->assertGreaterThanOrEqual(10, Category::count());
        $this->assertGreaterThanOrEqual(4, VendorProfile::count());
        $this->assertGreaterThanOrEqual(8, Product::count());
        $this->assertGreaterThanOrEqual(2, DeliveryAgentProfile::count());
        $this->assertGreaterThanOrEqual(2, Coupon::count());

        $this->assertEquals('VaishnoMart', Setting::get('platform_name'));
    }
}
