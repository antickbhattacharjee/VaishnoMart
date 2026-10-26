<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id',
        'area_name',
        'postal_code',
        'radius_km',
        'polygon_coordinates',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'radius_km' => 'decimal:2',
            'polygon_coordinates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }
}
