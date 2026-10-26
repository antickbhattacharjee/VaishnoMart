<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected int $length;
    protected int $expiryMinutes;
    protected int $maxAttempts;

    public function __construct()
    {
        $this->length = (int) env('OTP_LENGTH', 6);
        $this->expiryMinutes = (int) env('OTP_EXPIRY_MINUTES', 10);
        $this->maxAttempts = (int) env('OTP_MAX_ATTEMPTS', 5);
    }

    /**
     * Generate a cryptographically secure random numeric OTP for a specific order and stage.
     */
    public function generate(Order $order, string $stage, ?User $user = null): array
    {
        // Expire any existing unverified OTPs for this order & stage
        OtpVerification::where('order_id', $order->id)
            ->where('stage', $stage)
            ->where('is_verified', false)
            ->delete();

        $min = (int) pow(10, $this->length - 1);
        $max = (int) pow(10, $this->length) - 1;
        $plainOtp = (string) random_int($min, $max);

        $otpRecord = OtpVerification::create([
            'order_id' => $order->id,
            'stage' => $stage,
            'user_id' => $user?->id,
            'otp_hash' => Hash::make($plainOtp),
            'plain_otp' => $plainOtp,
            'expires_at' => Carbon::now()->addMinutes($this->expiryMinutes),
            'attempts_count' => 0,
            'max_attempts' => $this->maxAttempts,
            'is_verified' => false,
        ]);

        return [
            'plain_otp' => $plainOtp,
            'otp_record' => $otpRecord,
        ];
    }

    /**
     * Verify an OTP for an order and workflow stage.
     */
    public function verify(Order $order, string $stage, string $plainOtp): array
    {
        $plainOtp = trim($plainOtp);

        $otpRecord = OtpVerification::where('order_id', $order->id)
            ->where('stage', $stage)
            ->where('is_verified', false)
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'No active OTP verification session found for this stage.',
            ];
        }

        if ($otpRecord->isExpired()) {
            return [
                'success' => false,
                'message' => 'The OTP has expired. Please request a new OTP.',
            ];
        }

        if ($otpRecord->hasExceededMaxAttempts()) {
            return [
                'success' => false,
                'message' => 'Maximum verification attempts exceeded. Please generate a new OTP.',
            ];
        }

        $isValid = $otpRecord->verify($plainOtp);

        if (!$isValid) {
            $remaining = max(0, $otpRecord->max_attempts - $otpRecord->attempts_count);
            AuditLog::log('invalid_otp_attempt', 'Order', $order->id, [
                'stage' => $stage,
                'remaining' => $remaining,
            ]);
            return [
                'success' => false,
                'message' => "Invalid OTP entered. Remaining attempts: {$remaining}.",
            ];
        }

        AuditLog::log('otp_verified_successfully', 'Order', $order->id, [
            'stage' => $stage,
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
        ];
    }

    /**
     * Invalidate any pending OTP for an order and stage.
     */
    public function invalidate(Order $order, string $stage): void
    {
        OtpVerification::where('order_id', $order->id)
            ->where('stage', $stage)
            ->where('is_verified', false)
            ->delete();
    }
}
