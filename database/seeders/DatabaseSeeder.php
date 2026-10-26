<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\CustomerProfile;
use App\Models\DeliveryAgentProfile;
use App\Models\DeliveryFeeRule;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorServiceArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with comprehensive production-like initial data.
     */
    public function run(): void
    {
        // 1. Platform Settings
        Setting::set('platform_name', 'VaishnoMart', 'general', 'Platform Name');
        Setting::set('platform_tagline', 'Your Needs. Your Neighbourhood. Delivered.', 'general', 'Tagline');
        Setting::set('support_phone', '+91 98765 43210', 'support', 'Customer Support Helpline');
        Setting::set('support_email', 'support@vaishnomart.com', 'support', 'Support Email');
        Setting::set('default_commission_rate', '5.00', 'finance', 'Default Platform Commission %');
        Setting::set('vendor_acceptance_timeout_minutes', '5', 'orders', 'Timeout for vendor acceptance');

        // 2. Delivery Fee Rules
        DeliveryFeeRule::updateOrCreate(
            ['id' => 1],
            [
                'base_fee' => 25.00,
                'per_km_fee' => 8.00,
                'free_delivery_threshold' => 500.00,
                'surge_multiplier' => 1.00,
                'is_active' => true,
            ]
        );

        // 3. Super Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@vaishnomart.com'],
            [
                'name' => 'VaishnoMart SuperAdmin',
                'phone' => '9876543210',
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // 4. Categories
        $categoriesData = [
            [
                'name' => 'Drinking Water & Packaged Water',
                'slug' => 'drinking-water',
                'description' => '20L bubble jars, packaged mineral water bottles, and dispensers delivered quickly.',
                'icon' => 'bi-droplet-fill',
                'display_order' => 1,
            ],
            [
                'name' => 'Cloud Kitchens & Food Delivery',
                'slug' => 'cloud-kitchens',
                'description' => 'Freshly prepared meals, thalis, combos, and snacks from top neighbourhood cloud kitchens.',
                'icon' => 'bi-fire',
                'display_order' => 2,
            ],
            [
                'name' => 'Dry Fruits & Nuts',
                'slug' => 'dry-fruits',
                'description' => 'Premium California almonds, cashews, raisins, walnuts, pistachios, and trail mixes.',
                'icon' => 'bi-box2-heart-fill',
                'display_order' => 3,
            ],
            [
                'name' => 'Grocery & Daily Essentials',
                'slug' => 'grocery',
                'description' => 'Atta, rice, cooking oils, pulses, spices, packaged staples, and breakfast items.',
                'icon' => 'bi-basket2-fill',
                'display_order' => 4,
            ],
            [
                'name' => 'Fruits & Vegetables',
                'slug' => 'fruits-vegetables',
                'description' => 'Farm-fresh organic fruits, seasonal greens, and daily cooking vegetables.',
                'icon' => 'bi-egg-fried',
                'display_order' => 5,
            ],
            [
                'name' => 'Bakery & Confectionery',
                'slug' => 'bakery',
                'description' => 'Artisanal breads, cookies, muffins, cakes, and morning fresh bakery goods.',
                'icon' => 'bi-cup-hot-fill',
                'display_order' => 6,
            ],
            [
                'name' => 'Dairy Products',
                'slug' => 'dairy',
                'description' => 'Fresh pasteurized milk, paneer, curd, butter, ghee, and buttermilk.',
                'icon' => 'bi-cup-straw',
                'display_order' => 7,
            ],
            [
                'name' => 'Household Essentials',
                'slug' => 'household',
                'description' => 'Cleaning liquids, dishwashing bars, detergents, paper towels, and home care.',
                'icon' => 'bi-house-check-fill',
                'display_order' => 8,
            ],
            [
                'name' => 'Personal Care Products',
                'slug' => 'personal-care',
                'description' => 'Soaps, shampoos, oral hygiene, skincare, and daily grooming essentials.',
                'icon' => 'bi-heart-pulse-fill',
                'display_order' => 9,
            ],
            [
                'name' => 'Stationery & Office Supplies',
                'slug' => 'stationery',
                'description' => 'Notebooks, pens, printer paper, desk organizers, and office supplies.',
                'icon' => 'bi-journal-bookmark-fill',
                'display_order' => 10,
            ],
            [
                'name' => 'Electronics & Accessories',
                'slug' => 'electronics',
                'description' => 'Mobile chargers, USB cables, earphones, power banks, and adapters.',
                'icon' => 'bi-plugin',
                'display_order' => 11,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $cat = Category::firstOrCreate(
                ['slug' => $cData['slug']],
                [
                    'name' => $cData['name'],
                    'description' => $cData['description'],
                    'icon' => $cData['icon'],
                    'is_active' => true,
                    'display_order' => $cData['display_order'],
                ]
            );
            $categories[$cData['slug']] = $cat;

            // Seed Commission Rule for each category (5%)
            CommissionRule::firstOrCreate(
                ['category_id' => $cat->id],
                [
                    'commission_type' => 'percentage',
                    'rate' => 5.00,
                    'is_active' => true,
                ]
            );
        }

        // 5. Vendor 1: Vaishno Aqua Pure (Drinking Water)
        $waterUser = User::firstOrCreate(
            ['email' => 'water@vaishnomart.com'],
            [
                'name' => 'Sunil Verma',
                'phone' => '9810011111',
                'role' => User::ROLE_VENDOR,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $waterVendor = VendorProfile::firstOrCreate(
            ['user_id' => $waterUser->id],
            [
                'primary_category_id' => $categories['drinking-water']->id,
                'business_name' => 'Vaishno Aqua Pure Hub',
                'owner_name' => 'Sunil Verma',
                'phone' => '9810011111',
                'email' => 'water@vaishnomart.com',
                'address' => 'Shop 14, Inner Circle, Connaught Place, New Delhi',
                'latitude' => 28.6328,
                'longitude' => 77.2195,
                'service_radius_km' => 12.00,
                'opening_time' => '07:00:00',
                'closing_time' => '22:00:00',
                'is_open' => true,
                'is_accepting_orders' => true,
                'prep_time_minutes' => 10,
                'status' => 'approved',
                'commission_rate' => 4.00,
                'tax_number' => '07AAAAA0000A1Z5',
            ]
        );

        VendorServiceArea::firstOrCreate(
            ['vendor_profile_id' => $waterVendor->id, 'area_name' => 'Connaught Place & Central Delhi'],
            ['postal_code' => '110001', 'radius_km' => 12.00, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $waterVendor->id, 'name' => '20L Commercial Bubble Water Jar'],
            [
                'category_id' => $categories['drinking-water']->id,
                'slug' => '20l-bubble-water-jar',
                'description' => 'Multi-stage RO, UV & Ozonated purified 20 litre drinking water can. Sanitized and sealed top.',
                'sku' => 'AQUA-20L-CAN',
                'base_price' => 80.00,
                'sale_price' => 75.00,
                'stock_quantity' => 120,
                'unit' => 'jar',
                'deposit_amount' => 150.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Volume' => '20 Litres', 'Purification' => 'RO + UV + Ozonation', 'Can Deposit' => '₹150 refundable'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $waterVendor->id, 'name' => 'Pack of 12 x 1L Packaged Mineral Water'],
            [
                'category_id' => $categories['drinking-water']->id,
                'slug' => 'pack-of-12-x-1l-mineral-water',
                'description' => 'Convenient carton containing 12 individual 1-litre bottles with added minerals.',
                'sku' => 'AQUA-1L-12PK',
                'base_price' => 240.00,
                'sale_price' => 220.00,
                'stock_quantity' => 85,
                'unit' => 'pack',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Bottles' => '12 x 1 Litre', 'Brand' => 'Bisleri Certified', 'Tamper Evident' => 'Yes'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        // 6. Vendor 2: Vaishno Rasoi Cloud Kitchen (Food Delivery)
        $kitchenUser = User::firstOrCreate(
            ['email' => 'kitchen@vaishnomart.com'],
            [
                'name' => 'Chef Harpreet Kaur',
                'phone' => '9810022222',
                'role' => User::ROLE_VENDOR,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $kitchenVendor = VendorProfile::firstOrCreate(
            ['user_id' => $kitchenUser->id],
            [
                'primary_category_id' => $categories['cloud-kitchens']->id,
                'business_name' => 'Vaishno Rasoi Cloud Kitchen',
                'owner_name' => 'Chef Harpreet Kaur',
                'phone' => '9810022222',
                'email' => 'kitchen@vaishnomart.com',
                'address' => 'Plot 8B, Middle Circle, Connaught Place, New Delhi',
                'latitude' => 28.6312,
                'longitude' => 77.2180,
                'service_radius_km' => 10.00,
                'opening_time' => '11:00:00',
                'closing_time' => '23:30:00',
                'is_open' => true,
                'is_accepting_orders' => true,
                'prep_time_minutes' => 20,
                'status' => 'approved',
                'commission_rate' => 6.00,
                'tax_number' => '07BBBBB1111B2Z6',
            ]
        );

        VendorServiceArea::firstOrCreate(
            ['vendor_profile_id' => $kitchenVendor->id, 'area_name' => 'Connaught Place & Central Zone'],
            ['postal_code' => '110001', 'radius_km' => 10.00, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $kitchenVendor->id, 'name' => 'Royal Shahi Paneer Deluxe Thali'],
            [
                'category_id' => $categories['cloud-kitchens']->id,
                'slug' => 'royal-shahi-paneer-deluxe-thali',
                'description' => 'Creamy rich Shahi Paneer, Dal Makhani, Jeera Rice, 3 Butter Phulkas, Fresh Salad, and Gulab Jamun.',
                'sku' => 'VRK-THALI-01',
                'base_price' => 260.00,
                'sale_price' => 239.00,
                'stock_quantity' => 50,
                'unit' => 'piece',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 20,
                'specifications' => ['Cuisine' => 'North Indian', 'Packaging' => 'Bento Box Hot Sealed', 'Serves' => '1 Person'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $kitchenVendor->id, 'name' => 'Hyderabadi Veg Dum Biryani Matka Pot'],
            [
                'category_id' => $categories['cloud-kitchens']->id,
                'slug' => 'hyderabadi-veg-dum-biryani',
                'description' => 'Aromatic long-grain basmati rice cooked on slow dum with royal spices and fresh veggies. Served with spiced Raita and Salan.',
                'sku' => 'VRK-BIRYANI-02',
                'base_price' => 220.00,
                'sale_price' => 199.00,
                'stock_quantity' => 40,
                'unit' => 'piece',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 15,
                'specifications' => ['Accompaniments' => 'Burani Raita & Mirchi Salan', 'Spiciness' => 'Medium High', 'Serves' => '1-2'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        // 7. Vendor 3: Shree Vaishno Dry Fruits & Nuts
        $dryFruitsUser = User::firstOrCreate(
            ['email' => 'dryfruits@vaishnomart.com'],
            [
                'name' => 'Ramesh Chander Goyal',
                'phone' => '9810033333',
                'role' => User::ROLE_VENDOR,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $dryFruitsVendor = VendorProfile::firstOrCreate(
            ['user_id' => $dryFruitsUser->id],
            [
                'primary_category_id' => $categories['dry-fruits']->id,
                'business_name' => 'Shree Vaishno Dry Fruits & Nuts',
                'owner_name' => 'Ramesh Chander Goyal',
                'phone' => '9810033333',
                'email' => 'dryfruits@vaishnomart.com',
                'address' => 'Shop 22, Shankar Market, Connaught Place, New Delhi',
                'latitude' => 28.6340,
                'longitude' => 77.2210,
                'service_radius_km' => 15.00,
                'opening_time' => '09:00:00',
                'closing_time' => '21:00:00',
                'is_open' => true,
                'is_accepting_orders' => true,
                'prep_time_minutes' => 10,
                'status' => 'approved',
                'commission_rate' => 5.00,
                'tax_number' => '07CCCCC2222C3Z7',
            ]
        );

        VendorServiceArea::firstOrCreate(
            ['vendor_profile_id' => $dryFruitsVendor->id, 'area_name' => 'Central & South Delhi'],
            ['postal_code' => '110001', 'radius_km' => 15.00, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $dryFruitsVendor->id, 'name' => 'Premium California Almonds (Badam) 500g'],
            [
                'category_id' => $categories['dry-fruits']->id,
                'slug' => 'california-almonds-500g',
                'description' => '100% natural, whole, crunchy, supreme grade California almonds in an airtight vacuum pouch.',
                'sku' => 'VDF-ALM-500',
                'base_price' => 520.00,
                'sale_price' => 479.00,
                'stock_quantity' => 150,
                'unit' => 'pack',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Net Weight' => '500 Grams', 'Grade' => 'Supreme Jumbo', 'Shelf Life' => '12 Months'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $dryFruitsVendor->id, 'name' => 'Whole Roasted Salted Cashews (Kaju) 250g'],
            [
                'category_id' => $categories['dry-fruits']->id,
                'slug' => 'roasted-salted-cashews-250g',
                'description' => 'Crispy roasted whole W240 cashews lightly dusted with Himalayan pink rock salt.',
                'sku' => 'VDF-CAS-250',
                'base_price' => 320.00,
                'sale_price' => 295.00,
                'stock_quantity' => 90,
                'unit' => 'pack',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Net Weight' => '250 Grams', 'Type' => 'W240 Roasted', 'Flavour' => 'Himalayan Pink Salt'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        // 8. Vendor 4: Vaishno Daily Supermarket (Grocery & Daily Essentials)
        $groceryUser = User::firstOrCreate(
            ['email' => 'grocery@vaishnomart.com'],
            [
                'name' => 'Deepak Agarwal',
                'phone' => '9810044444',
                'role' => User::ROLE_VENDOR,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $groceryVendor = VendorProfile::firstOrCreate(
            ['user_id' => $groceryUser->id],
            [
                'primary_category_id' => $categories['grocery']->id,
                'business_name' => 'Vaishno Daily Supermarket',
                'owner_name' => 'Deepak Agarwal',
                'phone' => '9810044444',
                'email' => 'grocery@vaishnomart.com',
                'address' => 'G-12, Outer Circle, Connaught Place, New Delhi',
                'latitude' => 28.6295,
                'longitude' => 77.2160,
                'service_radius_km' => 10.00,
                'opening_time' => '08:00:00',
                'closing_time' => '22:30:00',
                'is_open' => true,
                'is_accepting_orders' => true,
                'prep_time_minutes' => 15,
                'status' => 'approved',
                'commission_rate' => 5.00,
                'tax_number' => '07DDDDD3333D4Z8',
            ]
        );

        VendorServiceArea::firstOrCreate(
            ['vendor_profile_id' => $groceryVendor->id, 'area_name' => 'Connaught Place Local'],
            ['postal_code' => '110001', 'radius_km' => 10.00, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $groceryVendor->id, 'name' => 'Aashirvaad Shudh Chakki Atta 5kg'],
            [
                'category_id' => $categories['grocery']->id,
                'slug' => 'aashirvaad-shudh-chakki-atta-5kg',
                'description' => '100% pure whole wheat grain flour ground with traditional chakki process for soft rotis.',
                'sku' => 'VDS-ATTA-5KG',
                'base_price' => 260.00,
                'sale_price' => 245.00,
                'stock_quantity' => 100,
                'unit' => 'pack',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Weight' => '5 Kilograms', 'Grain' => 'Whole Wheat', 'Dietary' => 'Vegetarian'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        Product::firstOrCreate(
            ['vendor_profile_id' => $groceryVendor->id, 'name' => 'Fortune Sunlite Refined Sunflower Oil 1L Pouch'],
            [
                'category_id' => $categories['grocery']->id,
                'slug' => 'fortune-sunlite-sunflower-oil-1l',
                'description' => 'Light and healthy sunflower cooking oil enriched with Vitamins A & D.',
                'sku' => 'VDS-OIL-1L',
                'base_price' => 160.00,
                'sale_price' => 145.00,
                'stock_quantity' => 75,
                'unit' => 'pack',
                'deposit_amount' => 0.00,
                'prep_time_minutes' => 5,
                'specifications' => ['Volume' => '1 Litre', 'Nutrients' => 'Fortified with Vitamin A & D'],
                'is_active' => true,
                'is_approved' => true,
                'is_available' => true,
            ]
        );

        // 9. Delivery Agents
        $agent1 = User::firstOrCreate(
            ['email' => 'rohan@vaishnomart.com'],
            [
                'name' => 'Rohan Kumar',
                'phone' => '9820011111',
                'role' => User::ROLE_DELIVERY_AGENT,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        DeliveryAgentProfile::firstOrCreate(
            ['user_id' => $agent1->id],
            [
                'vehicle_type' => 'bike',
                'vehicle_number' => 'DL-01-AB-1234',
                'license_number' => 'DL-9820012345678',
                'is_online' => true,
                'is_available' => true,
                'current_latitude' => 28.6320,
                'current_longitude' => 77.2185,
                'status' => 'approved',
                'service_radius_km' => 15.00,
            ]
        );

        $agent2 = User::firstOrCreate(
            ['email' => 'vikram@vaishnomart.com'],
            [
                'name' => 'Vikram Singh',
                'phone' => '9820022222',
                'role' => User::ROLE_DELIVERY_AGENT,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        DeliveryAgentProfile::firstOrCreate(
            ['user_id' => $agent2->id],
            [
                'vehicle_type' => 'scooter',
                'vehicle_number' => 'DL-03-CD-5678',
                'license_number' => 'DL-9820087654321',
                'is_online' => true,
                'is_available' => true,
                'current_latitude' => 28.6300,
                'current_longitude' => 77.2170,
                'status' => 'approved',
                'service_radius_km' => 15.00,
            ]
        );

        // 10. Customer
        $customer = User::firstOrCreate(
            ['email' => 'customer@vaishnomart.com'],
            [
                'name' => 'Amit Sharma',
                'phone' => '9811223344',
                'role' => User::ROLE_CUSTOMER,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $custAddress = Address::firstOrCreate(
            ['user_id' => $customer->id, 'label' => 'Home'],
            [
                'recipient_name' => 'Amit Sharma',
                'recipient_phone' => '9811223344',
                'address_line1' => 'Flat 402, Block B, Connaught Residency',
                'address_line2' => 'Barakhamba Road',
                'landmark' => 'Near Metro Station Gate 3',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'postal_code' => '110001',
                'latitude' => 28.6315,
                'longitude' => 77.2200,
                'is_default' => true,
            ]
        );

        CustomerProfile::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'default_address_id' => $custAddress->id,
                'loyalty_points' => 150,
                'preferences' => ['veg_only' => true, 'preferred_slot' => 'morning'],
            ]
        );

        // 11. Promotional Coupons
        Coupon::firstOrCreate(
            ['code' => 'WELCOME50'],
            [
                'title' => 'Welcome Discount ₹50 Flat Off',
                'description' => 'Get flat ₹50 off on your first order above ₹199 across all stores.',
                'discount_type' => 'fixed',
                'discount_value' => 50.00,
                'min_order_value' => 199.00,
                'usage_limit_total' => 1000,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addMonths(6),
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'VAISHNO10'],
            [
                'title' => '10% Super Saver',
                'description' => 'Enjoy 10% instant discount on orders above ₹499 up to ₹100.',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_order_value' => 499.00,
                'max_discount_amount' => 100.00,
                'usage_limit_total' => 5000,
                'usage_limit_per_user' => 3,
                'is_active' => true,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addMonths(6),
            ]
        );
    }
}
