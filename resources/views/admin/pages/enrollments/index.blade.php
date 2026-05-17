@extends('admin.layouts.app')

@section('title', 'Enrollments')

@section('content')
    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.enrollments.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="User name or course..." class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Status</label>
                    <div class="admin-input-group">
                        <select id="status" name="status" class="admin-select">
                            <option value="">All</option>
                            @foreach (['active', 'expired', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">ST</span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Enrollments</h3>
                <p class="admin-page-copy">Track user access to free and paid courses.</p>
            </div>
            <a href="{{ route('admin.enrollments.create') }}" class="admin-btn admin-btn-primary">Create Enrollment</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Course</th>
                        <th>Access Type</th>
                        <th>Status</th>
                        <th>Started At</th>
                        <th>Completed At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>{{ $enrollment->id }}</td>
                            <td>{{ $enrollment->user?->name ?: '-' }}</td>
                            <td>{{ $enrollment->course?->title ?: '-' }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($enrollment->access_type) }}">{{ $enrollment->access_type }}</span></td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($enrollment->status) }}">{{ $enrollment->status }}</span></td>
                            <td>{{ optional($enrollment->started_at)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>{{ optional($enrollment->completed_at)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Enrollment actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="admin-action-link" title="View enrollment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <span>View enrollment</span>
                                        </a>
                                        <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" onsubmit="return confirm('Delete this enrollment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete enrollment">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete enrollment</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">No enrollment rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $enrollments->links() }}
        </div>
    </section>
@endsection
