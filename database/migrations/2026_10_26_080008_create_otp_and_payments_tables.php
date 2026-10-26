<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('stage')->index(); // vendor_acceptance, pickup, delivery
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('otp_hash');
            $table->string('plain_otp', 10)->nullable();
            $table->dateTime('expires_at');
            $table->integer('attempts_count')->default(0);
            $table->integer('max_attempts')->default(5);
            $table->boolean('is_verified')->default(false)->index();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('transaction_reference')->unique()->index();
            $table->string('payment_gateway')->default('cod'); // cod, razorpay, stripe, mock_gateway
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('pending')->index(); // pending, successful, failed, refunded
            $table->json('gateway_response')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('refund_reference')->unique()->index();
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->string('status')->default('pending')->index(); // pending, processed, failed
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type')->index(); // order_credit, commission_debit, settlement_payout, refund_adjustment
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('description');
            $table->string('reference_number')->unique()->index();
            $table->timestamps();
        });

        Schema::create('delivery_agent_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('base_earning', 10, 2)->default(0.00);
            $table->decimal('distance_incentive', 10, 2)->default(0.00);
            $table->decimal('tip_amount', 10, 2)->default(0.00);
            $table->decimal('total_earning', 10, 2);
            $table->string('status')->default('pending')->index(); // pending, settled
            $table->dateTime('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->string('settlement_reference')->unique()->index();
            $table->decimal('amount', 10, 2);
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->string('status')->default('pending')->index(); // pending, processing, paid, rejected
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_settlements');
        Schema::dropIfExists('delivery_agent_earnings');
        Schema::dropIfExists('vendor_transactions');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('otp_verifications');
    }
};
