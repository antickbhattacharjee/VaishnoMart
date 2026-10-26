<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgentEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_agent_id',
        'order_id',
        'base_earning',
        'distance_incentive',
        'tip_amount',
        'total_earning',
        'status',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'base_earning' => 'decimal:2',
            'distance_incentive' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'total_earning' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
