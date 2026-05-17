@extends('admin.layouts.app')

@section('title', 'Create Enrollment')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Enrollment</h2>
        <p class="admin-section-copy">Grant course access to a user.</p>

        <form action="{{ route('admin.enrollments.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>User</label>
                <div class="admin-input-group">
                    <select name="user_id" class="admin-select" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
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
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
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
                            <option value="{{ $order->id }}">{{ $order->order_no }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">ORD</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Access Type</label>
                <div class="admin-input-group">
                    <select name="access_type" class="admin-select">
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                        <option value="admin_grant">Admin Grant</option>
                    </select>
                    <span class="admin-input-addon">ACC</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
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
                <button type="submit" class="admin-btn admin-btn-primary">Save Enrollment</button>
                <a href="{{ route('admin.enrollments.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
