@extends('admin.layouts.app')

@section('title', 'Edit Notification')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Notification</h2>
        <p class="admin-section-copy">Update schedule, audience, popup behavior, and message details for this notification.</p>

        <form action="{{ route('admin.notifications.update', $notification) }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')
            @include('admin.pages.notifications._form', ['notification' => $notification])

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Notification</button>
                <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
