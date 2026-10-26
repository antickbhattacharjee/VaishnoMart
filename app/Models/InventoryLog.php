<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'vendor_profile_id',
        'quantity_change',
        'previous_quantity',
        'new_quantity',
        'type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'previous_quantity' => 'integer',
            'new_quantity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }
}
