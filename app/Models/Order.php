<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PENDING_VENDOR = 'pending_vendor';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_DELIVERY_REQUESTED = 'delivery_requested';
    public const STATUS_DELIVERY_ASSIGNED = 'delivery_assigned';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DELIVERY_FAILED = 'delivery_failed';
    public const STATUS_REFUND_PENDING = 'refund_pending';
    public const STATUS_REFUNDED = 'refunded';

    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING_PAYMENT => [self::STATUS_PENDING_VENDOR, self::STATUS_CANCELLED],
        self::STATUS_PENDING_VENDOR => [self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_CANCELLED],
        self::STATUS_ACCEPTED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY_FOR_PICKUP, self::STATUS_CANCELLED],
        self::STATUS_READY_FOR_PICKUP => [self::STATUS_DELIVERY_REQUESTED, self::STATUS_DELIVERY_ASSIGNED, self::STATUS_PICKED_UP],
        self::STATUS_DELIVERY_REQUESTED => [self::STATUS_DELIVERY_ASSIGNED, self::STATUS_CANCELLED],
        self::STATUS_DELIVERY_ASSIGNED => [self::STATUS_PICKED_UP, self::STATUS_DELIVERY_REQUESTED, self::STATUS_CANCELLED],
        self::STATUS_PICKED_UP => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_DELIVERY_FAILED],
        self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_DELIVERY_FAILED],
        self::STATUS_DELIVERY_FAILED => [self::STATUS_REFUND_PENDING, self::STATUS_OUT_FOR_DELIVERY, self::STATUS_CANCELLED],
        self::STATUS_REJECTED => [self::STATUS_PENDING_VENDOR, self::STATUS_CANCELLED],
        self::STATUS_CANCELLED => [self::STATUS_REFUND_PENDING],
        self::STATUS_REFUND_PENDING => [self::STATUS_REFUNDED],
        self::STATUS_DELIVERED => [],
        self::STATUS_REFUNDED => [],
    ];

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_profile_id',
        'delivery_agent_id',
        'status',
        'subtotal',
        'tax_amount',
        'delivery_fee',
        'discount_amount',
        'deposit_total',
        'total_amount',
        'platform_commission_amount',
        'vendor_earnings_amount',
        'delivery_agent_earnings_amount',
        'payment_method',
        'payment_status',
        'customer_notes',
        'vendor_rejection_reason',
        'cancellation_reason',
        'vendor_acceptance_deadline',
        'vendor_accepted_at',
        'prepared_at',
        'delivery_requested_at',
        'delivery_assigned_at',
        'picked_up_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'deposit_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'platform_commission_amount' => 'decimal:2',
            'vendor_earnings_amount' => 'decimal:2',
            'delivery_agent_earnings_amount' => 'decimal:2',
            'vendor_acceptance_deadline' => 'datetime',
            'vendor_accepted_at' => 'datetime',
            'prepared_at' => 'datetime',
            'delivery_requested_at' => 'datetime',
            'delivery_assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
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

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function deliveryAddress()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'delivery');
    }

    public function pickupAddress()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'pickup');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('id', 'asc');
    }

    public function vendorAssignments()
    {
        return $this->hasMany(VendorAssignment::class);
    }

    public function deliveryAssignments()
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function activeDeliveryAssignment()
    {
        return $this->hasOne(DeliveryAssignment::class)->whereIn('status', ['offered', 'accepted'])->latest('id');
    }

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest('id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($targetStatus, $allowed, true);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT, self::STATUS_PENDING_VENDOR => 'warning',
            self::STATUS_ACCEPTED, self::STATUS_PREPARING => 'info',
            self::STATUS_READY_FOR_PICKUP, self::STATUS_DELIVERY_REQUESTED, self::STATUS_DELIVERY_ASSIGNED => 'primary',
            self::STATUS_PICKED_UP, self::STATUS_OUT_FOR_DELIVERY => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_REJECTED, self::STATUS_CANCELLED, self::STATUS_DELIVERY_FAILED => 'danger',
            self::STATUS_REFUND_PENDING => 'warning',
            self::STATUS_REFUNDED => 'secondary',
            default => 'secondary',
        };
    }
}
