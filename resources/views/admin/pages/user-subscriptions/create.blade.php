@extends('admin.layouts.app')

@section('title', 'Assign Subscription Plan')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Assign Subscription Plan</h2>
        <p class="admin-section-copy">Give any student plan access without payment.</p>
        <form action="{{ route('admin.user-subscriptions.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            <div class="admin-field"><label>Student</label><select name="user_id" class="admin-select" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $selectedUserId) === (int) $user->id)>{{ $user->name }} ({{ $user->email }})</option>@endforeach</select></div>
            <div class="admin-field"><label>Plan</label><select name="plan_id" class="admin-select" required>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((int) old('plan_id') === (int) $plan->id)>{{ $plan->name }} — {{ $plan->duration_days }} days</option>@endforeach</select></div>
            <div class="admin-field"><label>Starts At</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="admin-input"></div>
            <div class="admin-field"><label>Expires At (optional)</label><input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="admin-input"></div>
            <div class="admin-field" style="grid-column: 1 / -1;"><label>Notes</label><textarea name="notes" rows="3" class="admin-input">{{ old('notes') }}</textarea></div>
            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button class="admin-btn admin-btn-primary" type="submit">Assign Plan</button>
                <a class="admin-btn admin-btn-secondary" href="{{ route('admin.user-subscriptions.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
