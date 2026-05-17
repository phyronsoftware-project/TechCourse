@extends('web.layouts.app')

@section('title', $course->title)

@php
    $previewLesson = $activeLesson;
    $previewVideo = $previewLesson?->video_url ?: $course->intro_video_url;
    $startLessonRoute = null;
    $checkoutRoute = route('courses.checkout', $course->slug ?: $course->id);
    $loginRedirectRoute = route('web.login', ['redirect' => $checkoutRoute]);

    if ($previewLesson) {
        $startLessonRoute = ($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) && !$previewLesson->is_preview
            ? (auth()->check() ? $checkoutRoute : $loginRedirectRoute)
            : route('learning.show', [$course->slug ?: $course->id, $previewLesson->slug ?: $previewLesson->id]);
    }

    $embedUrl = null;

    if ($previewVideo) {
        if (str_contains($previewVideo, 'watch?v=')) {
            $embedUrl = str_replace('watch?v=', 'embed/', $previewVideo);
        } elseif (str_contains($previewVideo, 'youtu.be/')) {
            $embedUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $previewVideo);
        } elseif (str_contains($previewVideo, 'vimeo.com/')) {
            $embedUrl = str_replace('vimeo.com/', 'player.vimeo.com/video/', $previewVideo);
        }
    }
    $resourceCount = $course->resources->count();
    $courseRouteKey = $course->slug ?: $course->id;
    $canAccessPaidResources = !($courseNeedsPayment ?? false) || ($hasCourseAccess ?? false);
    $visibleResources = $course->resources
        ->filter(fn ($resource) => $resource->is_free || $canAccessPaidResources)
        ->values();
@endphp

@section('content')
    <style>
        .learning-shell {
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding-bottom: 56px;
        }

        .learning-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 22px;
            align-items: start;
        }

        .learning-main {
            display: grid;
            gap: 20px;
        }

        .learning-content-card,
        .learning-comment-card,
        .learning-sidebar-card {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .learning-content-card {
            overflow: hidden;
        }

        .learning-video-frame {
            position: relative;
            aspect-ratio: 16 / 9;
            background: #edf1f5;
        }

        .learning-video-frame--linkable {
            cursor: pointer;
        }

        .learning-video-frame__link {
            position: absolute;
            inset: 0;
            z-index: 2;
        }

        .learning-video-frame iframe,
        .learning-video-frame video,
        .learning-video-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .learning-video-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: 22px;
            font-family: var(--font-lato);
        }

        .learning-content-body {
            position: relative;
            padding: 28px 36px 34px;
        }

        .learning-meta-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .learning-course-title {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1.6rem;
            line-height: 1.35;
        }

        .learning-lesson-title {
            margin: 10px 0 0;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 700;
        }

        .learning-description {
            margin: 14px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }

        .learning-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .learning-action-form {
            margin: 0;
        }

        .learning-action-chip {
            min-height: 40px;
            padding: 0 14px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f3f6fa;
            border: 1px solid #dbe6f1;
            color: #1e3a5f;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .learning-action-chip.is-active {
            background: #dbeafe;
            border-color: #bfd8fb;
            color: #155eef;
        }

        .learning-statline {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 16px;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .learning-comment-card {
            padding: 24px;
        }

        .learning-section-title {
            margin: 0 0 16px;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1.3rem;
        }

        .comment-form {
            position: relative;
        }

        .comment-composer {
            width: 100%;
            min-height: 130px;
            padding: 16px 88px 16px 18px;
            border-radius: 18px;
            border: 1px solid #dbe6f1;
            background: #f8fafc;
            color: #0f172a;
            font: inherit;
            resize: vertical;
        }

        .comment-send-btn {
            position: absolute;
            right: 12px;
            bottom: 12px;
            width: 56px;
            height: 56px;
            border: 1px solid #dbe6f1;
            border-radius: 16px;
            background: #1d4ed8;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(29, 78, 216, 0.22);
        }

        .comment-empty {
            margin-top: 16px;
            padding: 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px dashed #dbe6f1;
            color: #64748b;
            font-size: 14px;
        }

        .comment-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .comment-item {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid #e2eaf3;
            background: #fbfdff;
        }

        .comment-item__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .comment-item__meta {
            display: grid;
            gap: 4px;
        }

        .comment-item__head strong {
            color: #0f172a;
            font-size: 13px;
        }

        .comment-item__head span {
            color: #64748b;
            font-size: 11px;
        }

        .comment-item p {
            margin: 8px 0 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .comment-item__actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .comment-action-btn {
            min-height: 30px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid #dbe6f1;
            background: #ffffff;
            color: #173f87;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .comment-action-btn.is-danger {
            color: #b42318;
            border-color: #fecaca;
            background: #fff5f5;
        }

        .comment-edit-form {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }

        .comment-edit-form[hidden] {
            display: none !important;
        }

        .comment-edit-form textarea {
            width: 100%;
            min-height: 96px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #dbe6f1;
            background: #ffffff;
            color: #0f172a;
            font: inherit;
            resize: vertical;
        }

        .comment-edit-form__actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .resource-card {
            padding: 24px;
        }

        .resource-list {
            display: grid;
            gap: 12px;
        }

        .resource-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid #dbe6f1;
            background: #fbfdff;
        }

        .resource-item__main {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .resource-item__icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: #e9f2ff;
            color: #155eef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .resource-item__meta {
            min-width: 0;
            display: grid;
            gap: 4px;
        }

        .resource-item__meta strong {
            color: #0f172a;
            font-size: 14px;
            line-height: 1.45;
        }

        .resource-item__meta span {
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .resource-item__actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .resource-badge {
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef5fd;
            color: #2b4f77;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .resource-open-btn {
            min-height: 40px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid #dbe6f1;
            background: #ffffff;
            color: #173f87;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .learning-sidebar-card {
            padding: 0;
            position: sticky;
            top: 104px;
            background: #f2f4f7;
            overflow: hidden;
        }

        .learning-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 0;
            padding: 16px 16px 10px;
            color: #0f172a;
        }

        .learning-sidebar-head h3 {
            margin: 0;
            font-family: var(--font-lato);
            font-size: 14px;
            line-height: 1.45;
        }

        .learning-sidebar-head span {
            color: #64748b;
            font-size: 13px;
            white-space: nowrap;
        }

        .learning-lesson-list {
            display: grid;
            gap: 10px;
            max-height: 620px;
            overflow-y: auto;
            padding: 0 10px 10px;
            scrollbar-width: none;
        }

        .learning-lesson-list::-webkit-scrollbar {
            display: none;
        }

        .learning-lesson-item {
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: start;
            width: 100%;
            padding: 6px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .learning-lesson-item:hover,
        .learning-lesson-item.is-active {
            background: #eef5fd;
            border-color: #c7d9ee;
        }

        .learning-lesson-thumb {
            width: 86px;
            height: 76px;
            border-radius: 12px;
            overflow: hidden;
            background: #17456a;
            border: 1px solid #cfe0ee;
        }

        .learning-lesson-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .learning-lesson-meta {
            min-width: 0;
        }

        .learning-lesson-name {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
        }

        .learning-lesson-copy {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .learning-lesson-date {
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .learning-lesson-badge {
            min-height: 26px;
            padding: 0 10px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8eef6;
            color: #284768;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .learning-lesson-item.is-active .learning-lesson-badge {
            background: #d6e7ff;
            color: #1d4ed8;
        }

        .related-shell {
            margin-top: 34px;
        }

        .related-shell .course-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        @media (max-width: 1080px) {
            .learning-layout {
                grid-template-columns: 1fr;
            }

            .learning-sidebar-card {
                position: static;
            }

            .related-shell .course-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .learning-shell {
                width: min(100%, calc(100% - 20px));
            }

            .learning-meta-top {
                flex-direction: column;
            }

            .related-shell .course-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="learning-shell">
        <div class="learning-layout">
            <div class="learning-main">
                <div class="learning-content-card">
                    <div class="learning-video-frame {{ $startLessonRoute ? 'learning-video-frame--linkable' : '' }}">
                        @if ($embedUrl)
                            <iframe src="{{ $embedUrl }}" title="{{ $previewLesson?->title ?: $course->title }}" frameborder="0" allowfullscreen></iframe>
                        @elseif ($previewVideo)
                            <video controls src="{{ $previewVideo }}"></video>
                        @elseif ($course->thumbnail_url)
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                        @else
                            <div class="learning-video-empty">{{ __('Video Display') }}</div>
                        @endif

                        @if ($startLessonRoute)
                            <a href="{{ $startLessonRoute }}" class="learning-video-frame__link" aria-label="{{ __('Open lesson video') }}"></a>
                        @endif
                    </div>
                    <div class="learning-content-body" @if ($startLessonRoute) data-card-link="{{ $startLessonRoute }}" @endif>
                        <div class="learning-meta-top">
                            <div>
                                <h1 class="learning-course-title">{{ $previewLesson?->title ?: $course->title }}</h1>
                                <p class="learning-lesson-title">{{ $course->title }}</p>
                            </div>

                            @if ($previewLesson)
                                <a href="{{ $startLessonRoute }}" class="learning-action-chip">
                                    <i class="fa-solid fa-circle-play"></i>
                                    {{ (($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) && !$previewLesson->is_preview) ? __('Pay with ABA') : __('Start Lesson') }}
                                </a>
                            @endif
                        </div>

                        <p class="learning-description">
                            {{ $previewLesson?->description ?: ($course->description ?: ($course->short_description ?: __('Title + description + time post + like + save list + share section prepared for your course detail page.'))) }}
                        </p>

                        <div class="learning-statline">
                            <span><i class="fa-regular fa-clock"></i> {{ $course->duration_text ?: __('Flexible learning') }}</span>
                            <span><i class="fa-solid fa-book-open"></i> {{ $course->lessons->count() }} {{ __('Lessons') }}</span>
                            <span><i class="fa-solid fa-folder-open"></i> {{ $resourceCount }} {{ __('resources') }}</span>
                            <span><i class="fa-regular fa-calendar"></i> {{ optional($course->created_at)->format('d M Y') ?: __('New') }}</span>
                        </div>

                        <div class="learning-actions">
                            @auth
                                <form action="{{ route('courses.like', $courseRouteKey) }}" method="POST" class="learning-action-form">
                                    @csrf
                                    <button type="submit" class="learning-action-chip {{ $isLiked ? 'is-active' : '' }}">
                                        <i class="{{ $isLiked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i> {{ __('Like') }}
                                    </button>
                                </form>

                                <form action="{{ route('courses.save', $courseRouteKey) }}" method="POST" class="learning-action-form">
                                    @csrf
                                    <button type="submit" class="learning-action-chip {{ $isSaved ? 'is-active' : '' }}">
                                        <i class="{{ $isSaved ? 'fa-solid' : 'fa-regular' }} fa-bookmark"></i> {{ __('Save List') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('web.login', ['redirect' => route('courses.show', $courseRouteKey)]) }}" class="learning-action-chip">
                                    <i class="fa-regular fa-heart"></i> {{ __('Like') }}
                                </a>

                                <a href="{{ route('web.login', ['redirect' => route('courses.show', $courseRouteKey)]) }}" class="learning-action-chip">
                                    <i class="fa-regular fa-bookmark"></i> {{ __('Save List') }}
                                </a>
                            @endauth

                            <button type="button" class="learning-action-chip" data-share-url="{{ route('courses.show', $courseRouteKey) }}">
                                <i class="fa-solid fa-share-nodes"></i> {{ __('Share') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="learning-comment-card">
                    <h2 class="learning-section-title">{{ __('Users Comment') }}</h2>
                    @auth
                        <form action="{{ route('courses.comments.store', $courseRouteKey) }}" method="POST" class="comment-form">
                            @csrf
                            <textarea class="comment-composer" name="comment" placeholder="{{ __('Write comment about this course or lesson...') }}" required>{{ old('comment') }}</textarea>
                            <button type="submit" class="comment-send-btn" aria-label="Send comment">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    @else
                        <div class="comment-empty">
                            {{ __('Please login first to comment on this course.') }}
                            <a href="{{ route('web.login', ['redirect' => route('courses.show', $courseRouteKey)]) }}">{{ __('Login') }}</a>
                        </div>
                    @endauth

                    @if ($courseComments->isEmpty())
                        <div class="comment-empty">{{ __('No comments yet. Be the first one to share your thoughts about this course.') }}</div>
                    @else
                        <div class="comment-list">
                            @foreach ($courseComments as $comment)
                                <article class="comment-item">
                                    <div class="comment-item__head">
                                        <div class="comment-item__meta">
                                            <strong>{{ $comment->user?->name ?: __('User') }}</strong>
                                            <span>{{ optional($comment->created_at)->format('d M Y h:i A') ?: '-' }}</span>
                                        </div>

                                        @if (auth()->id() === $comment->user_id)
                                            <div class="comment-item__actions">
                                                <button type="button" class="comment-action-btn" data-comment-edit-toggle="course-{{ $comment->id }}">{{ __('Edit') }}</button>
                                                <form action="{{ route('courses.comments.destroy', [$courseRouteKey, $comment]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="comment-action-btn is-danger">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <p>{{ $comment->comment }}</p>

                                    @if (auth()->id() === $comment->user_id)
                                        <form action="{{ route('courses.comments.update', [$courseRouteKey, $comment]) }}" method="POST" class="comment-edit-form" id="comment-edit-course-{{ $comment->id }}" hidden>
                                            @csrf
                                            @method('PUT')
                                            <textarea name="comment" required>{{ $comment->comment }}</textarea>
                                            <div class="comment-edit-form__actions">
                                                <button type="button" class="comment-action-btn" data-comment-edit-cancel="course-{{ $comment->id }}">{{ __('Cancel') }}</button>
                                                <button type="submit" class="comment-action-btn">{{ __('Update') }}</button>
                                            </div>
                                        </form>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="learning-content-card resource-card">
                    <h2 class="learning-section-title">{{ __('Course Resources') }}</h2>

                    @if ($visibleResources->isEmpty())
                        <div class="comment-empty">{{ __('No PDF resources uploaded for this course yet.') }}</div>
                    @else
                        <div class="resource-list">
                            @foreach ($visibleResources as $resource)
                                @php
                                    $resourceLabel = $resource->lesson?->title
                                        ? __('Lesson') . ': ' . $resource->lesson->title
                                        : __('Course Overview');
                                @endphp
                                <div class="resource-item">
                                    <div class="resource-item__main">
                                        <span class="resource-item__icon">
                                            <i class="fa-regular fa-file-pdf"></i>
                                        </span>
                                        <div class="resource-item__meta">
                                            <strong>{{ $resource->title }}</strong>
                                            <span>{{ $resourceLabel }}</span>
                                        </div>
                                    </div>

                                    <div class="resource-item__actions">
                                        <span class="resource-badge">{{ $resource->is_free ? __('Free') : __('Paid') }}</span>
                                        @if ($resource->file_url)
                                            <a href="{{ $resource->file_url }}" target="_blank" class="resource-open-btn">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                {{ __('Open PDF') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <aside class="learning-sidebar-card">
                <div class="learning-sidebar-head">
                    <h3>{{ $course->title }}</h3>
                    <span>{{ __('Sort Lesson') }}</span>
                </div>

                @if ($course->lessons->count())
                    <div class="learning-lesson-list">
                        @foreach ($course->lessons as $lesson)
                            @php
                                $lessonLocked = ($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) && !$lesson->is_preview;
                                $lessonRoute = $lessonLocked
                                    ? (auth()->check() ? $checkoutRoute : $loginRedirectRoute)
                                    : route('learning.show', [$course->slug ?: $course->id, $lesson->slug ?: $lesson->id]);
                            @endphp
                            <a href="{{ $lessonRoute }}" class="learning-lesson-item {{ $previewLesson && $previewLesson->id === $lesson->id ? 'is-active' : '' }}">
                                <div class="learning-lesson-thumb">
                                    @if ($course->thumbnail_url)
                                        <img src="{{ $course->thumbnail_url }}" alt="{{ $lesson->title }}">
                                    @endif
                                </div>
                                <div class="learning-lesson-meta">
                                    <div class="learning-lesson-name">{{ $lesson->title }}</div>
                                    <p class="learning-lesson-copy">{{ \Illuminate\Support\Str::limit(strip_tags((string) $lesson->description), 10) }}</p>
                                    <div class="learning-lesson-date">{{ optional($lesson->created_at)->format('d M Y') ?: __('New') }}</div>
                                </div>
                                <span class="learning-lesson-badge">
                                    {{ $lesson->is_preview ? __('Free') : (($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) ? __('Locked') : gmdate('i:s', (int) ($lesson->duration_seconds ?: 0))) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="comment-empty">{{ __('No lesson published yet for this course.') }}</div>
                @endif
            </aside>
        </div>

    </section>
@endsection

@push('web_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-card-link]').forEach((card) => {
                card.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, form, textarea, input')) {
                        return;
                    }

                    const url = card.getAttribute('data-card-link');

                    if (url) {
                        window.location.href = url;
                    }
                });
            });

            document.querySelectorAll('[data-comment-edit-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-comment-edit-toggle');
                    document.getElementById(`comment-edit-${id}`)?.removeAttribute('hidden');
                });
            });

            document.querySelectorAll('[data-comment-edit-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-comment-edit-cancel');
                    document.getElementById(`comment-edit-${id}`)?.setAttribute('hidden', 'hidden');
                });
            });
        });
    </script>
@endpush
