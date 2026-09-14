<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'subscription_id',
        'coupon_id',
        'payment_no',
        'payment_provider',
        'transaction_id',
        'transaction_hash',
        'merchant_id',
        'req_time',
        'payment_option',
        'amount',
        'subtotal_amount',
        'discount_amount',
        'currency',
        'khqr_string',
        'khqr_md5',
        'bakong_response',
        'status',
        'checkout_url',
        'abapay_deeplink',
        'khqr_deeplink',
        'qr_image_url',
        'response_payload',
        'callback_payload',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'bakong_response' => 'array',
            'response_payload' => 'array',
            'callback_payload' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCoupon::class, 'coupon_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class)->latest('id');
    }

    // Keep Bakong status checks readable inside services and controllers.
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expired_at !== null && $this->expired_at->isPast() && ! $this->isSuccess());
    }
}
