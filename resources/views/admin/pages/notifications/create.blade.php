@extends('admin.layouts.app')

@section('title', 'Create Notification')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Notification</h2>
        <p class="admin-section-copy">Create a general broadcast or a specific user notification for web and app flow.</p>

        <form action="{{ route('admin.notifications.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @include('admin.pages.notifications._form')

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Notification</button>
                <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
