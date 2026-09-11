@extends('admin.layouts.app')

@section('title', 'Grant Course Access')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Grant Course Access</h2>
        <p class="admin-section-copy">Give a user direct course access without requiring a payment.</p>

        <form action="{{ route('admin.enrollments.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>User</label>
                <div class="admin-input-group">
                    <select name="user_id" class="admin-select" required>
                        @foreach ($users as $user)
                            {{-- Keep the selected user after validation or when opened from Users. --}}
                            <option value="{{ $user->id }}" @selected((int) old('user_id', $selectedUserId) === (int) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">USR</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Course</label>
                <div class="admin-input-group">
                    <select name="course_id" class="admin-select" required>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id') === (int) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CRS</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Order</label>
                <div class="admin-input-group">
                    <select name="order_id" class="admin-select">
                        <option value="">No order</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" @selected((int) old('order_id') === (int) $order->id)>{{ $order->order_no }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">ORD</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Access Type</label>
                <div class="admin-input-group">
                    <select name="access_type" class="admin-select">
                        {{-- Default to an admin grant while retaining existing access types. --}}
                        <option value="admin_grant" @selected(old('access_type', 'admin_grant') === 'admin_grant')>Admin Grant</option>
                        <option value="free" @selected(old('access_type') === 'free')>Free</option>
                        <option value="paid" @selected(old('access_type') === 'paid')>Paid</option>
                    </select>
                    <span class="admin-input-addon">ACC</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="expired" @selected(old('status') === 'expired')>Expired</option>
                        <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <span class="admin-input-addon">ST</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Started At</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at') }}" class="admin-input">
            </div>

            <div class="admin-field">
                <label>Completed At</label>
                <input type="datetime-local" name="completed_at" value="{{ old('completed_at') }}" class="admin-input">
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Grant Course Access</button>
                <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
