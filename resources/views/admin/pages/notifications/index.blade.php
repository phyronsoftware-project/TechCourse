@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
    @if (! $setupReady)
        <section class="dashboard-panel rounded-[24px] border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <h3 class="text-base font-semibold">Notification tables are not ready yet</h3>
            <p class="mt-2 text-sm">Please run the SQL or migration for <code>system_notifications</code> and <code>notification_reads</code> first. After that, admin can manage general and specific notifications here.</p>
        </section>
    @endif

    <section class="admin-filter-card mt-4 p-6">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title, message, user" class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="type">Type</label>
                    <div class="admin-input-group">
                        <select id="type" name="type" class="admin-select">
                            <option value="">All</option>
                            <option value="general" @selected(($filters['type'] ?? '') === 'general')>General</option>
                            <option value="specific" @selected(($filters['type'] ?? '') === 'specific')>Specific</option>
                        </select>
                        <span class="admin-input-addon">TP</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="channel">Channel</label>
                    <div class="admin-input-group">
                        <select id="channel" name="channel" class="admin-select">
                            <option value="">All</option>
                            <option value="web" @selected(($filters['channel'] ?? '') === 'web')>Web</option>
                            <option value="app" @selected(($filters['channel'] ?? '') === 'app')>App</option>
                            <option value="all" @selected(($filters['channel'] ?? '') === 'all')>All</option>
                        </select>
                        <span class="admin-input-addon">CH</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="audience">Audience</label>
                    <div class="admin-input-group">
                        <select id="audience" name="audience" class="admin-select">
                            <option value="">All</option>
                            <option value="all" @selected(($filters['audience'] ?? '') === 'all')>All</option>
                            <option value="users" @selected(($filters['audience'] ?? '') === 'users')>Users</option>
                            <option value="admins" @selected(($filters['audience'] ?? '') === 'admins')>Admins</option>
                        </select>
                        <span class="admin-input-addon">AU</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Status</label>
                    <div class="admin-input-group">
                        <select id="status" name="status" class="admin-select">
                            <option value="">All</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                        <span class="admin-input-addon">ST</span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table mt-4">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Notification Management</h3>
                <p class="admin-page-copy">Create general broadcast notifications or specific user notifications for web and app experience.</p>
            </div>
            <a href="{{ route('admin.notifications.create') }}" class="admin-btn admin-btn-primary">Create Notification</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Recipient</th>
                        <th>Channel</th>
                        <th>Audience</th>
                        <th>Style</th>
                        <th>Status</th>
                        <th>Schedule</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr>
                            <td>{{ $notification->id }}</td>
                            <td>
                                <span class="admin-status-badge admin-status-badge-{{ $notification->type === 'general' ? 'active' : 'web' }}">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $notification->title }}</strong>
                                <div class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($notification->message, 70) }}</div>
                            </td>
                            <td>
                                @if ($notification->type === 'specific')
                                    <div>{{ $notification->user?->name ?: '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $notification->user?->email ?: '' }}</div>
                                @else
                                    <span class="admin-chip">{{ ucfirst($notification->audience) }}</span>
                                @endif
                            </td>
                            <td>{{ strtoupper($notification->channel) }}</td>
                            <td>{{ ucfirst($notification->audience) }}</td>
                            <td>{{ ucfirst($notification->style) }}</td>
                            <td>
                                <span class="admin-status-badge admin-status-badge-{{ $notification->is_active ? 'active' : 'inactive' }}">
                                    {{ $notification->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-slate-600">
                                    <div>{{ $notification->starts_at ? $notification->starts_at->timezone($notificationTimezone)->format('Y-m-d H:i') : 'No start' }}</div>
                                    <div>{{ $notification->ends_at ? $notification->ends_at->timezone($notificationTimezone)->format('Y-m-d H:i') : 'No end' }}</div>
                                </div>
                            </td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Notification actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.notifications.edit', $notification) }}" class="admin-action-link" title="Edit notification">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit notification</span>
                                        </a>
                                        <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('Delete this notification?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete notification">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete notification</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="admin-empty">No notifications found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $notifications->links() }}
        </div>
    </section>
@endsection
