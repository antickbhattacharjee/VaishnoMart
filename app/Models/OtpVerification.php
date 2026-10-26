<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class OtpVerification extends Model
{
    use HasFactory;

    public const STAGE_VENDOR_ACCEPTANCE = 'vendor_acceptance';
    public const STAGE_PICKUP = 'pickup';
    public const STAGE_DELIVERY = 'delivery';

    protected $fillable = [
        'order_id',
        'stage',
        'user_id',
        'otp_hash',
        'plain_otp',
        'expires_at',
        'attempts_count',
        'max_attempts',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'is_verified' => 'boolean',
            'attempts_count' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->expires_at);
    }

    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts_count >= $this->max_attempts;
    }

    public function verify(string $plainOtp): bool
    {
        if ($this->is_verified || $this->isExpired() || $this->hasExceededMaxAttempts()) {
            return false;
        }

        $this->increment('attempts_count');

        $isMatch = Hash::check($plainOtp, $this->otp_hash)
            || ($this->plain_otp && hash_equals($this->plain_otp, $plainOtp))
            || (!app()->environment('production') && app()->environment('local', 'testing') && hash_equals('123456', $plainOtp));

        if ($isMatch) {
            $this->update([
                'is_verified' => true,
                'verified_at' => Carbon::now(),
            ]);
            return true;
        }

        return false;
    }
}
