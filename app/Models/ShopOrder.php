<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopOrder extends Model
{
    protected $fillable = [
        'user_id',
        'province_id',
        'province_name',
        'order_no',
        'total_amount',
        'subtotal_amount',
        'delivery_fee',
        'currency',
        'status',
        'delivery_status',
        'payment_method',
        'paid_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'delivery_status' => 'string',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ShopPayment::class);
    }
}
