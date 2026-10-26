<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'vendor_profile_id',
        'delivery_agent_id',
        'vendor_rating',
        'vendor_review',
        'delivery_rating',
        'delivery_review',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'vendor_rating' => 'integer',
            'delivery_rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }
}
