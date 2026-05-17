@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (['Revenue Report', 'Course Sales Report', 'User Registration Report', 'Popular Courses', 'Payment Status Report', 'Enrollment Report'] as $section)
            <article class="dashboard-panel rounded-[30px] p-5 sm:p-6">
                <div class="admin-page-header">
                    <div>
                        <h3 class="admin-page-title">{{ $section }}</h3>
                        <p class="admin-page-copy">Report UI placeholder ready for chart/data integration.</p>
                    </div>
                    <span class="admin-chip">Report</span>
                </div>
            </article>
        @endforeach
    </section>
@endsection
