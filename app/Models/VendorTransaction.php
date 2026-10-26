<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    use HasFactory;

    public const TYPE_ORDER_CREDIT = 'order_credit';
    public const TYPE_COMMISSION_DEBIT = 'commission_debit';
    public const TYPE_SETTLEMENT_PAYOUT = 'settlement_payout';
    public const TYPE_REFUND_ADJUSTMENT = 'refund_adjustment';

    protected $fillable = [
        'vendor_profile_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
