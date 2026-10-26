<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_profile_id',
        'status',
        'distance_km',
        'rank',
        'offered_at',
        'expires_at',
        'responded_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'rank' => 'integer',
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }
}
