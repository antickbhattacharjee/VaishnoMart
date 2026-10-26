<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'code',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount_amount',
        'usage_limit_total',
        'usage_limit_per_user',
        'usage_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit_total' => 'integer',
            'usage_limit_per_user' => 'integer',
            'usage_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isValidForUser(int $userId, float $orderAmount, ?int $vendorProfileId = null): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Coupon is inactive.'];
        }

        $now = Carbon::now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return ['valid' => false, 'message' => 'Coupon is not yet active.'];
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return ['valid' => false, 'message' => 'Coupon has expired.'];
        }

        if ($this->vendor_profile_id !== null && $vendorProfileId !== null && $this->vendor_profile_id !== $vendorProfileId) {
            return ['valid' => false, 'message' => 'This coupon is valid only for a specific vendor store.'];
        }

        if ($orderAmount < (float) $this->min_order_value) {
            return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ₹' . number_format($this->min_order_value, 2)];
        }

        if ($this->usage_limit_total !== null && $this->usage_count >= $this->usage_limit_total) {
            return ['valid' => false, 'message' => 'Coupon total usage limit reached.'];
        }

        $userUsage = $this->redemptions()->where('user_id', $userId)->count();
        if ($userUsage >= $this->usage_limit_per_user) {
            return ['valid' => false, 'message' => 'You have already reached the maximum usage for this coupon.'];
        }

        return ['valid' => true, 'message' => 'Coupon applied successfully.'];
    }

    public function calculateDiscount(float $orderSubtotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($orderSubtotal * (float) $this->discount_value) / 100;
            if ($this->max_discount_amount !== null && $discount > (float) $this->max_discount_amount) {
                $discount = (float) $this->max_discount_amount;
            }
            return round($discount, 2);
        }

        // fixed
        return min((float) $this->discount_value, $orderSubtotal);
    }
}
