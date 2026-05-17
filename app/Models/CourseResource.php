<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'is_free',
        'is_downloadable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_free' => 'boolean',
            'is_downloadable' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_path) {
                    return null;
                }

                if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
                    return $this->file_path;
                }

                if (str_starts_with($this->file_path, 'storage/')) {
                    return '/' . ltrim($this->file_path, '/');
                }

                return '/storage/' . ltrim($this->file_path, '/');
            },
        );
    }
}
