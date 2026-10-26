<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('delivery_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending_vendor')->index();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('deposit_total', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('platform_commission_amount', 10, 2)->default(0.00);
            $table->decimal('vendor_earnings_amount', 10, 2)->default(0.00);
            $table->decimal('delivery_agent_earnings_amount', 10, 2)->default(0.00);
            $table->string('payment_method')->default('cod'); // cod, online, upi
            $table->string('payment_status')->default('pending')->index(); // pending, paid, failed, refunded
            $table->text('customer_notes')->nullable();
            $table->text('vendor_rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('vendor_acceptance_deadline')->nullable();
            $table->dateTime('vendor_accepted_at')->nullable();
            $table->dateTime('prepared_at')->nullable();
            $table->dateTime('delivery_requested_at')->nullable();
            $table->dateTime('delivery_assigned_at')->nullable();
            $table->dateTime('picked_up_at')->nullable();
            $table->dateTime('out_for_delivery_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('deposit_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('type')->default('delivery'); // pickup, delivery
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('previous_status')->nullable();
            $table->string('new_status')->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role')->nullable();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('status')->default('offered')->index(); // offered, accepted, rejected, expired
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('rank')->default(1);
            $table->dateTime('offered_at');
            $table->dateTime('expires_at');
            $table->dateTime('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('delivery_agent_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('offered')->index(); // offered, accepted, rejected, cancelled, completed
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->dateTime('offered_at');
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('pickup_verified_at')->nullable();
            $table->dateTime('delivery_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_assignments');
        Schema::dropIfExists('vendor_assignments');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
