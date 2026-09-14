@extends('admin.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.subscription-plans.index') }}" class="admin-filter-grid">
            <div class="admin-field admin-filter-field-wide">
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="admin-input" placeholder="Plan name or slug...">
            </div>
            <div class="admin-filter-actions">
                <button class="admin-btn admin-btn-primary">Filter</button>
                <a class="admin-btn admin-btn-secondary" href="{{ route('admin.subscription-plans.index') }}">Reset</a>
            </div>
        </form>
    </section>
    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div><h3 class="admin-page-title">Subscription Plans</h3><p class="admin-page-copy">Manage plan pricing, duration, and included courses.</p></div>
            <a class="admin-btn admin-btn-primary" href="{{ route('admin.subscription-plans.create') }}">Create Plan</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Days</th><th>Courses</th><th>Subscribers</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td>{{ $plan->id }}</td><td>{{ $plan->name }}</td><td>{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</td>
                            <td>{{ $plan->duration_days }}</td><td>{{ $plan->courses_count }}</td><td>{{ $plan->subscriptions_count }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ $plan->status }}">{{ $plan->status }}</span></td>
                            <td>
                                <a class="admin-btn admin-btn-secondary" href="{{ route('admin.subscription-plans.edit', $plan) }}">Edit</a>
                                <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan?')">
                                    @csrf @method('DELETE')
                                    <button class="admin-btn admin-btn-secondary" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="admin-empty">No subscription plans yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $plans->links() }}</div>
    </section>
@endsection
