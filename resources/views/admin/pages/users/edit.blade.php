@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit User</h2>
        <p class="admin-section-copy">User update flow is not implemented yet, but this page now matches the admin form template.</p>

        <div class="admin-form-grid mt-6">
            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" value="{{ $user->name ?? ('User #' . $recordId) }}" class="admin-input" disabled>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Email</label>
                <div class="admin-input-group">
                    <input type="text" value="{{ $user->email ?? 'No email' }}" class="admin-input" disabled>
                    <span class="admin-input-addon">@</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Role</label>
                <div class="admin-input-group">
                    <input type="text" value="{{ $user->role ?? 'user' }}" class="admin-input" disabled>
                    <span class="admin-input-addon">RL</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <input type="text" value="{{ $user->status ?? 'active' }}" class="admin-input" disabled>
                    <span class="admin-input-addon">ST</span>
                </div>
            </div>
        </div>
    </section>
@endsection
