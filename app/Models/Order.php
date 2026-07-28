<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_type', // online, outdoor
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'total_amount',
        'discount_amount',
        'final_amount',
        'coupon_code',
        'status', // pending, paid, processing, completed, cancelled
        'payment_bill_code',
        'payment_ref',
        'paid_at',
        'created_by', // sales agent user id
        'shipping_courier',
        'shipping_service',
        'shipping_cost',
        'shipping_postcode',
        'shipping_city',
        'shipping_state',
        'tracking_code',
        'easyparcel_order_id',
        'guest_token',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
