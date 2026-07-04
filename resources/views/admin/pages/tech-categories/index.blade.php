@extends('admin.layouts.app')

@section('title', 'Tech Categories')

@section('content')
    @if (!empty($tableMissing))
        <section class="admin-filter-card p-6 mb-6">
            <p class="text-sm text-amber-700">
                Tech categories table is missing on this server. Please run the SQL for `tech_categories` and `tech_details` first.
            </p>
        </section>
    @endif

    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.tech-categories.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Category name, subtitle, icon..." class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Status</label>
                    <div class="admin-input-group">
                        <select id="status" name="status" class="admin-select">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                        <span class="admin-input-addon">ST</span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.tech-categories.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Tech Categories</h3>
                <p class="admin-page-copy">Manage technology groups for the public technology page.</p>
            </div>
            <a href="{{ route('admin.tech-categories.create') }}" class="admin-btn admin-btn-primary">Create Category</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Subtitle</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Total Technology</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-lg text-blue-600">
                                    <i class="{{ $category->icon }}"></i>
                                </div>
                            </td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->subtitle ?: '-' }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($category->status) }}">{{ $category->status }}</span></td>
                            <td>{{ $category->sort_order }}</td>
                            <td>{{ $category->technologies_count }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Category actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.tech-details.index', ['category_id' => $category->id]) }}" class="admin-action-link" title="View technology">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <span>View technology</span>
                                        </a>
                                        <a href="{{ route('admin.tech-categories.edit', $category) }}" class="admin-action-link" title="Edit category">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit category</span>
                                        </a>
                                        <form action="{{ route('admin.tech-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this tech category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete category">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete category</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">No tech category rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $categories->links() }}
        </div>
    </section>
@endsection
