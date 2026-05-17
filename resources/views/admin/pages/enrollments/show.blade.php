@extends('admin.layouts.app')

@section('title', 'Enrollment Details')

@section('content')
    <section class="admin-form-card p-6">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Enrollment Details</h3>
                <p class="admin-page-copy">{{ $enrollment->user?->name ?: '-' }} • {{ $enrollment->course?->title ?: '-' }}</p>
            </div>
            <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">Back</a>
        </div>

        <table class="admin-meta-table">
            <tr><th>ID</th><td>{{ $enrollment->id }}</td></tr>
            <tr><th>User</th><td>{{ $enrollment->user?->name ?: '-' }}</td></tr>
            <tr><th>Course</th><td>{{ $enrollment->course?->title ?: '-' }}</td></tr>
            <tr><th>Access Type</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($enrollment->access_type) }}">{{ $enrollment->access_type }}</span></td></tr>
            <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($enrollment->status) }}">{{ $enrollment->status }}</span></td></tr>
            <tr><th>Started At</th><td>{{ optional($enrollment->started_at)->format('Y-m-d H:i') ?: '-' }}</td></tr>
            <tr><th>Completed At</th><td>{{ optional($enrollment->completed_at)->format('Y-m-d H:i') ?: '-' }}</td></tr>
        </table>
    </section>
@endsection
