<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'phone', 'address', 'city', 'province', 'postal_code', 'avatar', 'telegram_id', 'telegram_username', 'telegram_photo_url', 'role', 'status', 'password', 'email_verified_at', 'notification_muted', 'app_language', 'app_sound_enabled', 'app_vibrate_enabled'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_muted' => 'boolean',
            'app_sound_enabled' => 'boolean',
            'app_vibrate_enabled' => 'boolean',
        ];
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(CourseFavorite::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(CourseSave::class);
    }

    public function shopFavorites(): HasMany
    {
        return $this->hasMany(ShopFavorite::class);
    }

    public function shopCartItems(): HasMany
    {
        return $this->hasMany(ShopCartItem::class);
    }

    public function lessonComments(): HasMany
    {
        return $this->hasMany(LessonComment::class);
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function notificationReads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! filled($this->avatar)) {
                    return null;
                }

                if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                    return $this->avatar;
                }

                if (str_starts_with($this->avatar, 'storage/')) {
                    return asset($this->avatar);
                }

                return Storage::url($this->avatar);
            },
        );
    }
}
