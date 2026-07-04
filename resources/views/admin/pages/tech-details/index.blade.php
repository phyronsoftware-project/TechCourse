@extends('admin.layouts.app')

@section('title', 'Tech Detail')

@section('content')
    @if (!empty($tableMissing))
        <section class="admin-filter-card p-6 mb-6">
            <p class="text-sm text-amber-700">
                Tech tables are missing on this server. Please run the SQL for `tech_categories` and `tech_details` first.
            </p>
        </section>
    @endif

    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.tech-details.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Technology name, title, website..." class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="category_id">Category</label>
                    <div class="admin-input-group">
                        <select id="category_id" name="category_id" class="admin-select">
                            <option value="">All</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">CT</span>
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
                    <a href="{{ route('admin.tech-details.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Tech Detail</h3>
                <p class="admin-page-copy">Manage technology cards that open under each category.</p>
            </div>
            <a href="{{ route('admin.tech-details.create') }}" class="admin-btn admin-btn-primary">Create Technology</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Website</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($technologies as $technology)
                        <tr>
                            <td>{{ $technology->id }}</td>
                            <td>{{ $technology->name }}</td>
                            <td>{{ $technology->category?->name ?: '-' }}</td>
                            <td>{{ $technology->title ?: '-' }}</td>
                            <td>
                                @if ($technology->website_url)
                                    <a href="{{ $technology->website_url }}" target="_blank" rel="noreferrer" class="text-blue-600 hover:text-blue-700">
                                        Open Link
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($technology->status) }}">{{ $technology->status }}</span></td>
                            <td>{{ $technology->sort_order }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Technology actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.tech-details.edit', $technology) }}" class="admin-action-link" title="Edit technology">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit technology</span>
                                        </a>
                                        <form action="{{ route('admin.tech-details.destroy', $technology) }}" method="POST" onsubmit="return confirm('Delete this technology detail?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete technology">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete technology</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">No technology detail rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $technologies->links() }}
        </div>
    </section>
@endsection
