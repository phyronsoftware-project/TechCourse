<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected string $table = 'system_notifications';

    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $setupReady = $this->notificationService->tablesReady();
        $filters = $request->only(['search', 'type', 'channel', 'status', 'audience']);
        $notifications = new LengthAwarePaginator([], 0, 10);

        if ($setupReady) {
            $query = SystemNotification::query()
                ->with(['user:id,name,email', 'creator:id,name'])
                ->latest('id');

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            }

            foreach (['type', 'channel', 'audience'] as $filterKey) {
                if ($request->filled($filterKey)) {
                    $query->where($filterKey, $request->string($filterKey)->toString());
                }
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            }

            $notifications = $query->paginate(10)->withQueryString();
        }

        return view('admin.pages.notifications.index', [
            'pageTitle' => 'Notification Management',
            'notifications' => $notifications,
            'filters' => $filters,
            'setupReady' => $setupReady,
            'notificationTimezone' => $this->notificationTimezone(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.notifications.create', [
            'pageTitle' => 'Create Notification',
            'users' => $this->users(),
            'notificationTimezone' => $this->notificationTimezone(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->notificationService->tablesReady()) {
            return redirect()
                ->route('admin.notifications.index')
                ->with('error', 'Notification tables are not created yet. Please run the SQL command first.');
        }

        $data = $this->validatedData($request);
        $payload = $this->payload($data, $request);

        if ($payload['type'] === 'specific') {
            $this->notificationService->createSpecificNotification($payload);
        } else {
            $this->notificationService->createGeneralNotification($payload);
        }

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    public function edit(SystemNotification $notification): View
    {
        return view('admin.pages.notifications.edit', [
            'pageTitle' => 'Edit Notification',
            'notification' => $notification,
            'users' => $this->users(),
            'recordId' => $notification->id,
            'notificationTimezone' => $this->notificationTimezone(),
        ]);
    }

    public function update(Request $request, SystemNotification $notification): RedirectResponse
    {
        $data = $this->validatedData($request);
        $payload = $this->payload($data, $request);

        $notification->update($payload);

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification updated successfully.');
    }

    public function destroy(SystemNotification $notification): RedirectResponse
    {
        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    protected function validatedData(Request $request): array
    {
        $userRules = ['nullable', 'integer'];

        if (Schema::hasTable('users')) {
            $userRules[] = Rule::exists('users', 'id');
        }

        return $request->validate([
            'type' => ['required', Rule::in(['general', 'specific'])],
            'user_id' => array_merge($userRules, [Rule::requiredIf(fn () => $request->input('type') === 'specific')]),
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'channel' => ['required', Rule::in(['web', 'app', 'all'])],
            'audience' => ['required', Rule::in(['all', 'users', 'admins'])],
            'style' => ['required', Rule::in(['info', 'success', 'warning', 'error'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'send_as_popup' => ['nullable', 'boolean'],
        ]);
    }

    protected function payload(array $data, Request $request): array
    {
        return [
            'type' => $data['type'],
            'user_id' => $data['type'] === 'specific' ? $data['user_id'] : null,
            'created_by' => auth()->id(),
            'title' => $data['title'],
            'message' => $data['message'],
            'link_url' => $data['link_url'] ?: null,
            'channel' => $data['channel'],
            'audience' => $data['type'] === 'general' ? $data['audience'] : 'users',
            'style' => $data['style'],
            'trigger_event' => 'manual',
            'starts_at' => $this->normalizeDateTimeInput($data['starts_at'] ?? null),
            'ends_at' => $this->normalizeDateTimeInput($data['ends_at'] ?? null),
            'is_active' => $request->boolean('is_active'),
            'send_as_popup' => $request->boolean('send_as_popup'),
        ];
    }

    protected function normalizeDateTimeInput(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        return Carbon::parse($value, $this->notificationTimezone())->utc();
    }

    protected function notificationTimezone(): string
    {
        return 'Asia/Phnom_Penh';
    }

    protected function users()
    {
        if (! Schema::hasTable('users')) {
            return collect();
        }

        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }
}
