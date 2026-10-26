<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_profile_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'base_price',
        'sale_price',
        'stock_quantity',
        'unit',
        'prep_time_minutes',
        'deposit_amount',
        'specifications',
        'is_active',
        'is_approved',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'stock_quantity' => 'integer',
            'prep_time_minutes' => 'integer',
            'specifications' => 'array',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
        });
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->base_price) {
            return (float) $this->sale_price;
        }

        return (float) $this->base_price;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_approved', true)
            ->where('is_available', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function hasSufficientStock(int $requestedQuantity): bool
    {
        return $this->stock_quantity >= $requestedQuantity;
    }
}
