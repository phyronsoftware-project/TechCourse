@extends('admin.layouts.app')

@section('title', 'Create Social Media Link')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Social Media Link</h2>
        <p class="admin-section-copy">Add a social media link for web or app clients and choose the matching icon from the dropdown.</p>

        <form action="{{ route('admin.social-media.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @include('admin.pages.social-media._form', ['link' => null, 'defaultPlatform' => $defaultPlatform, 'iconOptions' => $iconOptions])

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Social Link</button>
                <a href="{{ route('admin.social-media.index', ['platform' => $defaultPlatform]) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
