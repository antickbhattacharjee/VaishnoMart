<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->nullable()->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('discount_type')->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_value', 10, 2)->default(0.00);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('usage_limit_total')->nullable();
            $table->integer('usage_limit_per_user')->default(1);
            $table->integer('usage_count')->default(0);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->decimal('discount_applied', 10, 2);
            $table->timestamps();
        });

        Schema::create('delivery_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_fee', 10, 2)->default(25.00);
            $table->decimal('per_km_fee', 10, 2)->default(8.00);
            $table->decimal('free_delivery_threshold', 10, 2)->default(500.00);
            $table->decimal('surge_multiplier', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->nullable()->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('commission_type')->default('percentage'); // percentage, fixed
            $table->decimal('rate', 8, 2)->default(5.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('delivery_fee_rules');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
    }
};
