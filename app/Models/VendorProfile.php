<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'primary_category_id',
        'business_name',
        'owner_name',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'service_radius_km',
        'opening_time',
        'closing_time',
        'is_open',
        'is_accepting_orders',
        'prep_time_minutes',
        'logo',
        'cover_image',
        'status',
        'rejection_reason',
        'commission_rate',
        'tax_number',
        'bank_details',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'service_radius_km' => 'decimal:2',
            'is_open' => 'boolean',
            'is_accepting_orders' => 'boolean',
            'prep_time_minutes' => 'integer',
            'commission_rate' => 'decimal:2',
            'bank_details' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function primaryCategory()
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function serviceAreas()
    {
        return $this->hasMany(VendorServiceArea::class);
    }

    public function transactions()
    {
        return $this->hasMany(VendorTransaction::class);
    }

    public function settlements()
    {
        return $this->hasMany(VendorSettlement::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeAvailableForOrders($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('is_open', true)
            ->where('is_accepting_orders', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->is_open && $this->is_accepting_orders;
    }

    /**
     * Calculate Haversine distance in kilometers from vendor to customer coordinates.
     */
    public function distanceFrom(float $latitude, float $longitude): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Check if given coordinates are within the vendor's serviceable radius.
     */
    public function canServeLocation(float $latitude, float $longitude): bool
    {
        $distance = $this->distanceFrom($latitude, $longitude);
        return $distance <= (float) $this->service_radius_km;
    }

    public function getWalletBalanceAttribute(): float
    {
        $latest = $this->transactions()->latest('id')->first();
        return $latest ? (float) $latest->balance_after : 0.00;
    }
}
