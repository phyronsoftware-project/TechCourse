@extends('admin.layouts.app')

@section('title', 'Create Subscription Plan')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Subscription Plan</h2>
        <p class="admin-section-copy">Set price, access duration, and included courses.</p>
        <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @include('admin.pages.subscription-plans._form')
            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button class="admin-btn admin-btn-primary" type="submit">Save Plan</button>
                <a class="admin-btn admin-btn-secondary" href="{{ route('admin.subscription-plans.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
