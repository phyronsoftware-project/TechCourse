<?php

namespace App\Services;

use App\Models\NotificationRead;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function tablesReady(): bool
    {
        return Schema::hasTable('system_notifications') && Schema::hasTable('notification_reads');
    }

    public function getHeaderNotifications(?User $user, string $channel = 'web', int $limit = 8): array
    {
        if (! $this->tablesReady()) {
            return [
                'items' => collect(),
                'unreadCount' => 0,
            ];
        }

        $items = $this->visibleQuery($user, $channel)
            ->with(['user:id,name,email', 'creator:id,name'])
            ->latest('id')
            ->limit($limit)
            ->get();

        $unreadCount = 0;

        if ($user) {
            $readIds = NotificationRead::query()
                ->where('user_id', $user->id)
                ->whereIn('notification_id', $items->pluck('id'))
                ->pluck('notification_id')
                ->all();

            $readLookup = array_flip($readIds);

            $items->each(function (SystemNotification $notification) use ($readLookup): void {
                $notification->setAttribute('is_read', isset($readLookup[$notification->id]));
            });

            $unreadCount = $this->visibleQuery($user, $channel)
                ->whereNotExists(function ($query) use ($user): void {
                    $query->selectRaw('1')
                        ->from('notification_reads')
                        ->whereColumn('notification_reads.notification_id', 'system_notifications.id')
                        ->where('notification_reads.user_id', $user->id);
                })
                ->count();
        } else {
            $items->each(fn (SystemNotification $notification) => $notification->setAttribute('is_read', true));
        }

        return [
            'items' => $items,
            'unreadCount' => $unreadCount,
        ];
    }

    public function markAllVisibleAsRead(User $user, string $channel = 'web'): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $notificationIds = $this->visibleQuery($user, $channel)->pluck('system_notifications.id');

        if ($notificationIds->isEmpty()) {
            return 0;
        }

        $existingIds = NotificationRead::query()
            ->where('user_id', $user->id)
            ->whereIn('notification_id', $notificationIds)
            ->pluck('notification_id');

        $missingIds = $notificationIds->diff($existingIds)->values();

        if ($missingIds->isEmpty()) {
            return 0;
        }

        $now = now();

        NotificationRead::query()->insert(
            $missingIds->map(fn ($notificationId) => [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );

        return $missingIds->count();
    }

    public function createSpecificNotification(array $data): ?SystemNotification
    {
        if (! $this->tablesReady()) {
            return null;
        }

        return SystemNotification::query()->create([
            'type' => 'specific',
            'user_id' => $data['user_id'],
            'created_by' => $data['created_by'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'link_url' => $data['link_url'] ?? null,
            'channel' => $data['channel'] ?? 'web',
            'audience' => $data['audience'] ?? 'users',
            'style' => $data['style'] ?? 'info',
            'trigger_event' => $data['trigger_event'] ?? 'manual',
            'send_as_popup' => $data['send_as_popup'] ?? true,
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);
    }

    public function createGeneralNotification(array $data): ?SystemNotification
    {
        if (! $this->tablesReady()) {
            return null;
        }

        return SystemNotification::query()->create([
            'type' => 'general',
            'user_id' => null,
            'created_by' => $data['created_by'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'link_url' => $data['link_url'] ?? null,
            'channel' => $data['channel'] ?? 'web',
            'audience' => $data['audience'] ?? 'all',
            'style' => $data['style'] ?? 'info',
            'trigger_event' => $data['trigger_event'] ?? 'manual',
            'send_as_popup' => $data['send_as_popup'] ?? true,
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);
    }

    public function createAuthEventNotification(User $user, string $event): ?SystemNotification
    {
        $messages = [
            'login' => [
                'title' => 'Login Successful',
                'message' => 'Welcome back, ' . $user->name . '. Your account login was completed successfully.',
                'style' => 'success',
            ],
            'register' => [
                'title' => 'Welcome to TechCourse',
                'message' => 'Hi ' . $user->name . ', your account is ready. Start exploring your courses now.',
                'style' => 'success',
            ],
        ];

        if (! isset($messages[$event])) {
            return null;
        }

        return $this->createSpecificNotification([
            'user_id' => $user->id,
            'title' => $messages[$event]['title'],
            'message' => $messages[$event]['message'],
            'channel' => 'web',
            'audience' => 'users',
            'style' => $messages[$event]['style'],
            'trigger_event' => $event,
            'send_as_popup' => true,
            'is_active' => true,
        ]);
    }

    public function flashPopupNotification(User $user, string $event): void
    {
        $notification = $this->createAuthEventNotification($user, $event);

        if (! $notification) {
            return;
        }

        session()->flash($notification->style, $notification->message);
    }

    protected function visibleQuery(?User $user, string $channel = 'web'): Builder
    {
        $query = SystemNotification::query()
            ->where('is_active', true)
            ->whereIn('channel', [$channel, 'all'])
            ->where(function (Builder $builder): void {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });

        if (! $user) {
            return $query
                ->where('type', 'general')
                ->where('audience', 'all');
        }

        $audiences = ['all'];
        $audiences[] = in_array($user->role, ['admin', 'super_admin'], true) ? 'admins' : 'users';

        return $query->where(function (Builder $builder) use ($user, $audiences): void {
            $builder
                ->where(function (Builder $general) use ($audiences): void {
                    $general
                        ->where('type', 'general')
                        ->whereIn('audience', $audiences);
                })
                ->orWhere(function (Builder $specific) use ($user): void {
                    $specific
                        ->where('type', 'specific')
                        ->where('user_id', $user->id);
                });
        });
    }
}
