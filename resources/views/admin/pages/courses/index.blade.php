@extends('admin.layouts.app')

@section('title', 'Courses')

@section('content')
    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.courses.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Course title or slug..." class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Status</label>
                    <div class="admin-input-group">
                        <select id="status" name="status" class="admin-select">
                            <option value="">All</option>
                            @foreach (['draft', 'published', 'archived'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">ST</span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.courses.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Courses</h3>
                <p class="admin-page-copy">Manage free and paid courses across all categories.</p>
            </div>
            <a href="{{ route('admin.courses.create') }}" class="admin-btn admin-btn-primary">Create Course</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Free/Paid</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Total Lessons</th>
                        <th>Total Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>
                                @if ($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="admin-thumb">
                                @else
                                    <span class="admin-thumb-empty">No Img</span>
                                @endif
                            </td>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->category?->name ?: '-' }}</td>
                            <td>${{ number_format((float) $course->price, 2) }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $course->is_free ? 'active' : 'pending' }}">{{ $course->is_free ? 'Free' : 'Paid' }}</span></td>
                            <td>{{ ucfirst($course->level) }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($course->status) }}">{{ $course->status }}</span></td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $course->is_published ? 'published' : 'draft' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span></td>
                            <td>{{ $course->total_lessons }}</td>
                            <td>{{ $course->total_students }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Course actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.courses.show', $course) }}" class="admin-action-link" title="View course">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <span>View course</span>
                                        </a>
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="admin-action-link" title="Edit course">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit course</span>
                                        </a>
                                        <a href="{{ route('admin.courses.lessons.index', $course) }}" class="admin-action-link" title="Course lessons">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 6h16" />
                                                <path d="M4 12h16" />
                                                <path d="M4 18h10" />
                                            </svg>
                                            <span>Course lessons</span>
                                        </a>
                                        <a href="{{ route('admin.courses.resources.index', $course) }}" class="admin-action-link" title="Course resources">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 3h7v7" />
                                                <path d="M10 14 21 3" />
                                                <path d="M21 14v7h-7" />
                                                <path d="M3 10V3h7" />
                                            </svg>
                                            <span>Course resources</span>
                                        </a>
                                        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Delete this course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete course">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete course</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="admin-empty">No course rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $courses->links() }}
        </div>
    </section>
@endsection
