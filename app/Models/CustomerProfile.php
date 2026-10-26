<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_address_id',
        'loyalty_points',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'preferences' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function defaultAddress()
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }
}
