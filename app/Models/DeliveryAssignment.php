<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_agent_id',
        'status',
        'distance_km',
        'offered_at',
        'responded_at',
        'pickup_verified_at',
        'delivery_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'offered_at' => 'datetime',
            'responded_at' => 'datetime',
            'pickup_verified_at' => 'datetime',
            'delivery_verified_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }
}
