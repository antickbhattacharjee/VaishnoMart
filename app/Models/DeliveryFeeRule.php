<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFeeRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_fee',
        'per_km_fee',
        'free_delivery_threshold',
        'surge_multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'per_km_fee' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'surge_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function calculateFee(float $distanceKm, float $subtotal): float
    {
        $rule = static::where('is_active', true)->first();
        if (!$rule) {
            // Default platform fallback
            if ($subtotal >= 500) {
                return 0.00;
            }
            return 25.00 + ($distanceKm * 8.00);
        }

        if ($subtotal >= (float) $rule->free_delivery_threshold) {
            return 0.00;
        }

        $fee = (float) $rule->base_fee + ($distanceKm * (float) $rule->per_km_fee);
        $fee *= (float) $rule->surge_multiplier;

        return round($fee, 2);
    }
}
