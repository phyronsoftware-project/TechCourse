@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')
    @if (! $setupReady)
        <section class="dashboard-panel rounded-[24px] border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <h3 class="text-base font-semibold">Banner table is not ready yet</h3>
            <p class="mt-2 text-sm">Please run the SQL or migration for the new <code>banners</code> table first. After that, this page will manage web and app banners normally.</p>
        </section>
    @endif

    <section class="admin-filter-card mt-4 p-6">
        <form method="GET" action="{{ route('admin.banners.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title, subtitle, linked course" class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="platform">Platform</label>
                    <div class="admin-input-group">
                        <select id="platform" name="platform" class="admin-select">
                            <option value="">All</option>
                            @foreach (['web' => 'Web', 'app' => 'App'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['platform'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">PL</span>
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
                    <a href="{{ route('admin.banners.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table mt-4">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Banner Management</h3>
                <p class="admin-page-copy">Manage homepage, dashboard, web, and app banners linked to courses.</p>
            </div>
            <a href="{{ route('admin.banners.create', ['platform' => $filters['platform'] ?? 'web']) }}" class="admin-btn admin-btn-primary">Create Banner</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Preview</th>
                        <th>Platform</th>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th>Schedule</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr>
                            <td>{{ $banner->id }}</td>
                            <td>
                                @if ($banner->image_url)
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="admin-thumb">
                                @else
                                    <span class="admin-chip">No Image</span>
                                @endif
                            </td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $banner->platform }}">{{ strtoupper($banner->platform) }}</span></td>
                            <td>
                                <strong>{{ $banner->title }}</strong>
                                <div class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($banner->subtitle ?: '-', 60) }}</div>
                            </td>
                            <td>{{ $banner->course?->title ?: '-' }}</td>
                            <td>
                                <span class="admin-status-badge admin-status-badge-{{ $banner->is_active ? 'active' : 'inactive' }}">
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $banner->sort_order }}</td>
                            <td>
                                <div class="text-sm text-slate-600">
                                    <div>{{ optional($banner->starts_at)->format('Y-m-d H:i') ?: 'No start' }}</div>
                                    <div>{{ optional($banner->ends_at)->format('Y-m-d H:i') ?: 'No end' }}</div>
                                </div>
                            </td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Banner actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.banners.edit', $banner) }}" class="admin-action-link" title="Edit banner">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit banner</span>
                                        </a>
                                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Delete this banner?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete banner">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete banner</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="admin-empty">No banners found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $banners->links() }}
        </div>
    </section>
@endsection
