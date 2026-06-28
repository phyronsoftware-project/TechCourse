@extends('admin.layouts.app')

@section('title', 'Social Media')

@section('content')
    @if (! $setupReady)
        <section class="dashboard-panel rounded-[24px] border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <h3 class="text-base font-semibold">Social media table is not ready yet</h3>
            <p class="mt-2 text-sm">Please run the SQL command for the new <code>social_media_links</code> table first. After that, this page will manage web and app social media links normally.</p>
        </section>
    @endif

    <section class="admin-filter-card mt-4 p-6">
        <form method="GET" action="{{ route('admin.social-media.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, URL, icon" class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="platform">Platform</label>
                    <div class="admin-input-group">
                        <select id="platform" name="platform" class="admin-select">
                            <option value="">All</option>
                            <option value="web" @selected(($filters['platform'] ?? '') === 'web')>Web</option>
                            <option value="app" @selected(($filters['platform'] ?? '') === 'app')>App</option>
                            <option value="all" @selected(($filters['platform'] ?? '') === 'all')>All</option>
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
                    <a href="{{ route('admin.social-media.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table mt-4">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Social Media Management</h3>
                <p class="admin-page-copy">Manage social media links for web and app clients, including icon, URL, sorting, and status.</p>
            </div>
            <a href="{{ route('admin.social-media.create', ['platform' => $filters['platform'] ?? 'web']) }}" class="admin-btn admin-btn-primary">Create Social Link</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Platform</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Sort</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($links as $link)
                        <tr>
                            <td>{{ $link->id }}</td>
                            <td>
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#dbe6f1] bg-[#eef5fd] text-[18px] text-[#173f87]">
                                    <i class="{{ $link->icon }}"></i>
                                </span>
                            </td>
                            <td>
                                <strong>{{ $link->name }}</strong>
                                <div class="mt-1 text-xs text-slate-500">{{ $iconLabels[$link->icon] ?? $link->icon }}</div>
                            </td>
                            <td>
                                <span class="admin-status-badge admin-status-badge-{{ $link->platform === 'all' ? 'web' : $link->platform }}">
                                    {{ strtoupper($link->platform) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-[#173f87] underline-offset-2 hover:underline">
                                    {{ \Illuminate\Support\Str::limit($link->url, 48) }}
                                </a>
                            </td>
                            <td>
                                <span class="admin-status-badge admin-status-badge-{{ $link->is_active ? 'active' : 'inactive' }}">
                                    {{ $link->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $link->sort_order }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Social media actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.social-media.edit', $link) }}" class="admin-action-link" title="Edit social media link">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit link</span>
                                        </a>
                                        <form action="{{ route('admin.social-media.destroy', $link) }}" method="POST" onsubmit="return confirm('Delete this social media link?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete social media link">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete link</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">No social media links found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $links->links() }}
        </div>
    </section>
@endsection
