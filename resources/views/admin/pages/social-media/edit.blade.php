@extends('admin.layouts.app')

@section('title', 'Edit Social Media Link')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Social Media Link</h2>
        <p class="admin-section-copy">Update social media name, icon, URL, platform, and visibility.</p>

        <form action="{{ route('admin.social-media.update', $link) }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')
            @include('admin.pages.social-media._form', ['link' => $link, 'iconOptions' => $iconOptions])

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Social Link</button>
                <a href="{{ route('admin.social-media.index', ['platform' => $link->platform]) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
