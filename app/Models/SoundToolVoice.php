<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SoundToolVoice extends Model
{
    protected $fillable = [
        'provider',
        'provider_voice_id',
        'name',
        'language_code',
        'description',
        'category',
        'sample_audio_path',
        'preview_url',
        'labels',
        'meta',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'meta' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
