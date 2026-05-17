@extends('admin.layouts.app')

@section('title', 'Resources')

@section('content')
    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Course Resources / PDFs</h3>
                <p class="admin-page-copy">{{ $course->title }} resources and PDF attachments.</p>
            </div>
            <a href="{{ route('admin.courses.resources.create', $courseId) }}" class="admin-btn admin-btn-primary">Create Resource</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Lesson</th>
                        <th>File Type</th>
                        <th>File Size</th>
                        <th>Free/Paid</th>
                        <th>Downloadable</th>
                        <th>Sort Order</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resources as $resource)
                        <tr>
                            <td>{{ $resource->title }}</td>
                            <td>{{ $resource->lesson?->title ?: '-' }}</td>
                            <td>{{ $resource->file_type ?: '-' }}</td>
                            <td>{{ $resource->file_size ? number_format((int) $resource->file_size) . ' bytes' : '-' }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $resource->is_free ? 'active' : 'pending' }}">{{ $resource->is_free ? 'Free' : 'Paid' }}</span></td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $resource->is_downloadable ? 'active' : 'inactive' }}">{{ $resource->is_downloadable ? 'Downloadable' : 'Locked' }}</span></td>
                            <td>{{ $resource->sort_order }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Resource actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.courses.resources.edit', [$course, $resource]) }}" class="admin-action-link" title="Edit resource">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit resource</span>
                                        </a>
                                        @if ($resource->file_url)
                                            <a href="{{ $resource->file_url }}" target="_blank" class="admin-action-link" title="Open PDF">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14 3h7v7" />
                                                    <path d="M10 14 21 3" />
                                                    <path d="M21 14v7h-7" />
                                                    <path d="M3 10V3h7" />
                                                </svg>
                                                <span>Open file</span>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.courses.resources.destroy', [$course, $resource]) }}" method="POST" onsubmit="return confirm('Delete this resource?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete resource">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete resource</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">No resource rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
