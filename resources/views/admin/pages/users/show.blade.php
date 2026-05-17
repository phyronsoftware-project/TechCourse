@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
    <section class="admin-form-card p-6">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">User Details</h3>
                <p class="admin-page-copy">{{ $user->name }} • {{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">Back</a>
        </div>

        <table class="admin-meta-table">
            <tr><th>ID</th><td>{{ $user->id }}</td></tr>
            <tr><th>Name</th><td>{{ $user->name }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Phone</th><td>{{ $user->phone ?: '-' }}</td></tr>
            <tr><th>Role</th><td><span class="admin-status-badge admin-status-badge-{{ ($user->role ?: 'user') === 'admin' ? 'published' : 'pending' }}">{{ $user->role ?: 'user' }}</span></td></tr>
            <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($user->status ?: 'active') }}">{{ $user->status ?: 'active' }}</span></td></tr>
            <tr><th>Verified</th><td>{{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i') : 'No' }}</td></tr>
        </table>
    </section>
@endsection
