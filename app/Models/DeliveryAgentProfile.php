<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgentProfile extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'is_online',
        'is_available',
        'current_latitude',
        'current_longitude',
        'status',
        'rejection_reason',
        'service_radius_km',
        'bank_details',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'is_available' => 'boolean',
            'current_latitude' => 'decimal:8',
            'current_longitude' => 'decimal:8',
            'service_radius_km' => 'decimal:2',
            'bank_details' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'delivery_agent_id', 'user_id');
    }

    public function earnings()
    {
        return $this->hasMany(DeliveryAgentEarning::class, 'delivery_agent_id', 'user_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeAvailableForDelivery($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('is_online', true)
            ->where('is_available', true);
    }

    public function distanceFrom(float $latitude, float $longitude): ?float
    {
        if (is_null($this->current_latitude) || is_null($this->current_longitude)) {
            return null;
        }

        $earthRadius = 6371;

        $latFrom = deg2rad($this->current_latitude);
        $lonFrom = deg2rad($this->current_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }
}
