@extends('admin.layouts.app')

@section('title', 'Lessons')

@section('content')
    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Lessons</h3>
                <p class="admin-page-copy">{{ $course->title }} lesson structure and publishing flow.</p>
            </div>
            <a href="{{ route('admin.courses.lessons.create', $courseId) }}" class="admin-btn admin-btn-primary">Create Lesson</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sort Order</th>
                        <th>Title</th>
                        <th>Video Type</th>
                        <th>Duration</th>
                        <th>Preview</th>
                        <th>Published</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lessons as $lesson)
                        <tr>
                            <td>{{ $lesson->sort_order }}</td>
                            <td>{{ $lesson->title }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($lesson->video_type) }}">{{ $lesson->video_type }}</span></td>
                            <td>{{ $lesson->duration_seconds ? gmdate('H:i:s', (int) $lesson->duration_seconds) : '-' }}</td>
                            <td>{{ $lesson->is_preview ? 'Yes' : 'No' }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $lesson->is_published ? 'published' : 'draft' }}">{{ $lesson->is_published ? 'Published' : 'Draft' }}</span></td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Lesson actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.courses.lessons.edit', [$course, $lesson]) }}" class="admin-action-link" title="Edit lesson">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit lesson</span>
                                        </a>
                                        <form action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}" method="POST" onsubmit="return confirm('Delete this lesson?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete lesson">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete lesson</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty">No lesson rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
