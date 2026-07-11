<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPayment extends Model
{
    protected $fillable = [
        'shop_order_id',
        'user_id',
        'payment_no',
        'payment_provider',
        'transaction_id',
        'transaction_hash',
        'amount',
        'currency',
        'khqr_string',
        'khqr_md5',
        'bakong_response',
        'status',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bakong_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expired_at !== null && $this->expired_at->isPast() && $this->status !== 'success');
    }
}
