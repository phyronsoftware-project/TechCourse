@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
    <section class="admin-form-card p-6">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Review Details</h3>
                <p class="admin-page-copy">Placeholder details page for review ID: {{ $recordId }}</p>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="admin-btn admin-btn-secondary">Back</a>
        </div>

        <table class="admin-meta-table">
            <tr><th>Review ID</th><td>{{ $review->id }}</td></tr>
            <tr><th>User</th><td>{{ $review->user?->name ?: '-' }}</td></tr>
            <tr><th>Course</th><td>{{ $review->course?->title ?: '-' }}</td></tr>
            <tr><th>Rating</th><td>{{ $review->rating }}/5</td></tr>
            <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($review->status) }}">{{ $review->status }}</span></td></tr>
            <tr><th>Comment</th><td>{{ $review->comment ?: '-' }}</td></tr>
        </table>
    </section>
@endsection
