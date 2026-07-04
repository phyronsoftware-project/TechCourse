<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'title',
        'detail',
        'website_url',
        'status',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TechCategory::class, 'category_id');
    }
}
