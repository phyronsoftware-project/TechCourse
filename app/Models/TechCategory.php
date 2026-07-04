<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'icon',
        'status',
        'sort_order',
    ];

    public function technologies(): HasMany
    {
        return $this->hasMany(TechDetail::class, 'category_id');
    }
}
