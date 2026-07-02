<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopProduct extends Model
{
    protected $appends = [
        'image_url',
    ];

    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'description',
        'cost_price',
        'sale_price',
        'stock_qty',
        'image',
        'status',
        'sku',
        'barcode',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_qty' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShopProductImage::class, 'product_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ShopFavorite::class, 'product_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(ShopCartItem::class, 'product_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        $path = str_starts_with($this->image, 'storage/')
            ? ltrim(substr($this->image, strlen('storage/')), '/')
            : ltrim($this->image, '/');

        return route('media.public', ['path' => $path]);
    }
}
