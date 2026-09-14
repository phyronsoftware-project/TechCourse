@extends('admin.layouts.app')

@section('title', 'Subscribers')

@section('content')
    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.user-subscriptions.index') }}" class="admin-filter-grid">
            <div class="admin-field admin-filter-field-wide"><label>Search</label><input name="search" value="{{ request('search') }}" class="admin-input" placeholder="Student or plan..."></div>
            <div class="admin-field"><label>Status</label><select name="status" class="admin-select"><option value="">All</option>@foreach(['pending','active','expired','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="admin-filter-actions"><button class="admin-btn admin-btn-primary">Filter</button><a class="admin-btn admin-btn-secondary" href="{{ route('admin.user-subscriptions.index') }}">Reset</a></div>
        </form>
    </section>
    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div><h3 class="admin-page-title">Subscribers</h3><p class="admin-page-copy">View paid and admin-assigned subscription access.</p></div>
            <a class="admin-btn admin-btn-primary" href="{{ route('admin.user-subscriptions.create') }}">Assign Plan</a>
        </div>
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>ID</th><th>Student</th><th>Plan</th><th>Source</th><th>Status</th><th>Starts</th><th>Expires</th><th></th></tr></thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    @php $effectiveStatus = $subscription->isActive() ? 'active' : ($subscription->status === 'active' ? 'expired' : $subscription->status); @endphp
                    <tr>
                        <td>{{ $subscription->id }}</td><td>{{ $subscription->user?->name }}<br><small>{{ $subscription->user?->email }}</small></td><td>{{ $subscription->plan?->name }}</td><td>{{ $subscription->source }}</td>
                        <td><span class="admin-status-badge admin-status-badge-{{ $effectiveStatus }}">{{ $effectiveStatus }}</span></td>
                        <td>{{ optional($subscription->starts_at)->format('Y-m-d H:i') ?: '-' }}</td><td>{{ optional($subscription->expires_at)->format('Y-m-d H:i') ?: 'Never' }}</td>
                        <td>@if(!in_array($subscription->status, ['cancelled','expired'], true))<form method="POST" action="{{ route('admin.user-subscriptions.cancel', $subscription) }}" onsubmit="return confirm('Cancel this subscription?')">@csrf<button class="admin-btn admin-btn-secondary">Cancel</button></form>@endif</td>
                    </tr>
                @empty<tr><td colspan="8" class="admin-empty">No subscriptions yet.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="mt-5">{{ $subscriptions->links() }}</div>
    </section>
@endsection
