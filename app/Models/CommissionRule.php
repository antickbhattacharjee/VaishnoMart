<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'vendor_profile_id',
        'commission_type',
        'rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public static function calculateCommission(float $orderSubtotal, ?int $vendorProfileId = null, ?int $categoryId = null): float
    {
        // 1. Specific vendor rule
        if ($vendorProfileId) {
            $rule = static::where('is_active', true)
                ->where('vendor_profile_id', $vendorProfileId)
                ->first();
            if ($rule) {
                return $rule->compute($orderSubtotal);
            }
        }

        // 2. Specific category rule
        if ($categoryId) {
            $rule = static::where('is_active', true)
                ->where('category_id', $categoryId)
                ->first();
            if ($rule) {
                return $rule->compute($orderSubtotal);
            }
        }

        // 3. Fallback to vendor profile commission rate or platform standard 5%
        if ($vendorProfileId) {
            $vendor = VendorProfile::find($vendorProfileId);
            if ($vendor && $vendor->commission_rate > 0) {
                return round(($orderSubtotal * (float) $vendor->commission_rate) / 100, 2);
            }
        }

        return round(($orderSubtotal * 5.0) / 100, 2);
    }

    public function compute(float $amount): float
    {
        if ($this->commission_type === 'percentage') {
            return round(($amount * (float) $this->rate) / 100, 2);
        }

        return (float) $this->rate;
    }
}
