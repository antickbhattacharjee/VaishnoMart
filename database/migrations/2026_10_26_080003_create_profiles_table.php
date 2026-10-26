<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('default_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->integer('loyalty_points')->default(0);
            $table->json('preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('primary_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('business_name')->index();
            $table->string('owner_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->index();
            $table->decimal('longitude', 11, 8)->index();
            $table->decimal('service_radius_km', 6, 2)->default(10.00);
            $table->time('opening_time')->default('08:00:00');
            $table->time('closing_time')->default('22:00:00');
            $table->boolean('is_open')->default(true)->index();
            $table->boolean('is_accepting_orders')->default(true)->index();
            $table->integer('prep_time_minutes')->default(15);
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, suspended
            $table->text('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5.00%
            $table->string('tax_number')->nullable();
            $table->json('bank_details')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('area_name');
            $table->string('postal_code', 20)->nullable()->index();
            $table->decimal('radius_km', 6, 2)->nullable();
            $table->json('polygon_coordinates')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_agent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('vehicle_type')->default('bike');
            $table->string('vehicle_number')->nullable();
            $table->string('license_number')->nullable();
            $table->boolean('is_online')->default(false)->index();
            $table->boolean('is_available')->default(true)->index();
            $table->decimal('current_latitude', 10, 8)->nullable()->index();
            $table->decimal('current_longitude', 11, 8)->nullable()->index();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, suspended
            $table->text('rejection_reason')->nullable();
            $table->decimal('service_radius_km', 6, 2)->default(15.00);
            $table->json('bank_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_agent_profiles');
        Schema::dropIfExists('vendor_service_areas');
        Schema::dropIfExists('vendor_profiles');
        Schema::dropIfExists('customer_profiles');
    }
};
