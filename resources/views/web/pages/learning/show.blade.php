@extends('web.layouts.app')

@section('title', $activeLesson->title)

@php
    // Prefer uploaded lesson video file when lesson uses local upload.
    $uploadedVideoUrl = null;

    if (($activeLesson->video_type ?? null) === 'upload' && !empty($activeLesson->video_file)) {
        $videoFilePath = ltrim((string) $activeLesson->video_file, '/');
        $uploadedVideoUrl = \Illuminate\Support\Str::startsWith($videoFilePath, ['http://', 'https://'])
            ? $videoFilePath
            : route('media.public', ['path' => $videoFilePath]);
    }

    $videoUrl = $uploadedVideoUrl ?: ($activeLesson->video_url ?: $course->intro_video_url);
    $checkoutRoute = route('courses.checkout', $course->slug ?: $course->id);
    $loginRedirectRoute = route('web.login', ['redirect' => $checkoutRoute]);
    $lessonRouteKey = $activeLesson->slug ?: $activeLesson->id;
    $courseRouteKey = $course->slug ?: $course->id;
    $currentLessonRoute = route('learning.show', [$courseRouteKey, $lessonRouteKey]);
    $embedUrl = null;

    if ($videoUrl) {
        if (str_contains($videoUrl, 'watch?v=')) {
            $embedUrl = str_replace('watch?v=', 'embed/', $videoUrl);
        } elseif (str_contains($videoUrl, 'youtu.be/')) {
            $embedUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $videoUrl);
        } elseif (str_contains($videoUrl, 'vimeo.com/')) {
            $embedUrl = str_replace('vimeo.com/', 'player.vimeo.com/video/', $videoUrl);
        }
    }
    $canAccessPaidResources = !($courseNeedsPayment ?? false) || ($hasCourseAccess ?? false);
    $visibleResources = $course->resources
        ->filter(function ($resource) use ($activeLesson, $canAccessPaidResources) {
            if (!($resource->is_free || $canAccessPaidResources)) {
                return false;
            }

            return !$resource->lesson_id || (int) $resource->lesson_id === (int) $activeLesson->id;
        })
        ->values();
@endphp

@section('content')
    <style>
        .lesson-learning-shell {
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding-bottom: 56px;
        }

        .lesson-learning-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 22px;
            align-items: start;
        }

        .lesson-learning-main {
            display: grid;
            gap: 0;
        }

        .lesson-box,
        .lesson-sidebar-box {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .lesson-video-shell {
            overflow: hidden;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            border-bottom: 0;
            margin-bottom: -8px;
        }

        .lesson-video-frame {
            position: relative;
            aspect-ratio: 16 / 9;
            background: #edf1f5;
        }

        .lesson-video-frame iframe,
        .lesson-video-frame video {
            width: 100%;
            height: 100%;
            display: block;
        }

        .lesson-video-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: 22px;
            font-family: var(--font-lato);
        }

        .lesson-detail-box,
        .lesson-comment-box {
            padding: 24px;
        }

        .lesson-detail-box {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            padding-top: 30px;
            margin-bottom: 20px;
        }

        .lesson-comment-box,
        .lesson-resource-box {
            margin-top: 20px;
        }

        .lesson-head-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .lesson-course-name {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1.55rem;
            line-height: 1.35;
        }

        .lesson-name {
            margin: 10px 0 0;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 700;
        }

        .lesson-description {
            margin: 14px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }

        .lesson-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .lesson-chip-form {
            margin: 0;
        }

        .lesson-chip {
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

        .lesson-chip.is-active {
            background: #dbeafe;
            border-color: #bfd8fb;
            color: #155eef;
        }

        .lesson-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 16px;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .lesson-section-title {
            margin: 0 0 16px;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1.3rem;
        }

        .lesson-comment-form {
            position: relative;
        }

        .lesson-comment-area {
            width: 100%;
            min-height: 130px;
            padding: 16px 72px 16px 18px;
            border-radius: 22px;
            border: 1px solid #dbe6f1;
            background: #ffffff;
            color: #0f172a;
            font: inherit;
            resize: vertical;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .lesson-comment-send {
            position: absolute;
            right: 16px;
            bottom: 16px;
            width: 42px;
            height: 42px;
            border: 1px solid #dbe6f1;
            border-radius: 999px;
            background: #ffffff;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .lesson-comment-empty {
            margin-top: 16px;
            padding: 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px dashed #dbe6f1;
            color: #64748b;
            font-size: 14px;
        }

        .lesson-comment-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .lesson-comment-item {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid #e2eaf3;
            background: #fbfdff;
        }

        .lesson-comment-item__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .lesson-comment-item__meta {
            display: grid;
            gap: 4px;
        }

        .lesson-comment-item__identity {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .lesson-comment-item__avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            box-shadow: inset 0 0 0 1px #bfdbfe;
        }

        .lesson-comment-item__avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lesson-comment-item__head strong {
            color: #0f172a;
            font-size: 13px;
        }

        .lesson-comment-item__head span {
            color: #64748b;
            font-size: 11px;
        }

        .lesson-comment-item p {
            margin: 12px 0 0 56px;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .lesson-comment-item__actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .lesson-comment-action-btn {
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

        .lesson-comment-action-btn.is-danger {
            color: #b42318;
            border-color: #fecaca;
            background: #fff5f5;
        }

        .lesson-comment-edit-form {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }

        .lesson-comment-edit-form[hidden] {
            display: none !important;
        }

        .lesson-comment-edit-form textarea {
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

        .lesson-comment-edit-form__actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .lesson-resource-box {
            padding: 24px;
        }

        .lesson-resource-list {
            display: grid;
            gap: 12px;
        }

        .lesson-resource-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid #dbe6f1;
            background: #fbfdff;
        }

        .lesson-resource-item__main {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .lesson-resource-item__icon {
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

        .lesson-resource-item__meta {
            min-width: 0;
            display: grid;
            gap: 4px;
        }

        .lesson-resource-item__meta strong {
            color: #0f172a;
            font-size: 14px;
            line-height: 1.45;
        }

        .lesson-resource-item__meta span {
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .lesson-resource-item__actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .lesson-resource-badge {
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

        .lesson-resource-open-btn {
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

        .lesson-sidebar-box {
            padding: 10px 10px 12px;
            position: sticky;
            top: 104px;
            background: #ffffff;
            overflow: hidden;
            border-radius: 0;
            box-shadow: none;
        }

        .lesson-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 0;
            padding: 10px 10px 12px;
        }

        .lesson-sidebar-head h3 {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.45;
        }

        .lesson-sidebar-head span {
            color: #64748b;
            font-size: 13px;
            white-space: nowrap;
        }

        .lesson-list-shell {
            display: grid;
            gap: 10px;
            max-height: 620px;
            overflow-y: auto;
            padding: 0;
            scrollbar-width: none;
        }

        .lesson-list-shell::-webkit-scrollbar {
            display: none;
        }

        .lesson-list-row {
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr) auto;
            gap: 14px;
            align-items: start;
            width: 100%;
            padding: 12px;
            border-radius: 0;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            text-decoration: none;
            transition: 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .lesson-list-row:hover {
            background: #ffffff;
            border-color: #dbe6f1;
        }

        .lesson-list-row.is-active,
        .lesson-list-row.is-active:hover,
        .lesson-list-row.is-active:focus,
        .lesson-list-row.is-active:active,
        .lesson-list-row.is-active:visited {
            background: #ffffff !important;
            border-color: #dbe6f1 !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .lesson-list-thumb {
            width: 86px;
            height: 76px;
            border-radius: 0;
            overflow: hidden;
            background: #17456a;
            border: 1px solid #cfe0ee;
        }

        .lesson-list-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lesson-list-meta {
            min-width: 0;
        }

        .lesson-list-title {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
        }

        .lesson-list-copy {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .lesson-list-date {
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .lesson-list-badge {
            min-height: 26px;
            padding: 0 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #e8eef6;
            color: #284768;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            min-width: 104px;
        }

        .lesson-list-badge__mark {
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
        }

        .lesson-list-row.is-active .lesson-list-title,
        .lesson-list-row.is-active .lesson-list-copy,
        .lesson-list-row.is-active .lesson-list-date {
            color: inherit !important;
        }

        .lesson-list-row.is-active .lesson-list-badge,
        .lesson-list-row.is-active:hover .lesson-list-badge {
            background: #e8eef6 !important;
            color: #284768 !important;
        }

        @media (max-width: 1080px) {
            .lesson-learning-layout {
                grid-template-columns: 1fr;
            }

            .lesson-sidebar-box {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .lesson-learning-shell {
                width: min(100%, calc(100% - 20px));
            }

            .lesson-head-row {
                flex-direction: column;
            }
        }
    </style>

    <section class="lesson-learning-shell">
        <div class="lesson-learning-layout">
            <div class="lesson-learning-main">
                <div class="lesson-box lesson-video-shell">
                    <div class="lesson-video-frame">
                        @if ($embedUrl)
                            <iframe src="{{ $embedUrl }}" title="{{ $activeLesson->title }}" frameborder="0" allowfullscreen></iframe>
                        @elseif ($videoUrl)
                            <video controls playsinline preload="metadata" src="{{ $videoUrl }}"></video>
                        @else
                            <div class="lesson-video-empty">{{ __('Video Display') }}</div>
                        @endif
                    </div>
                </div>

                <div class="lesson-box lesson-detail-box" data-card-link="{{ $currentLessonRoute }}">
                    <div class="lesson-head-row">
                        <div>
                            <h1 class="lesson-course-name">{{ $course->title }}</h1>
                            <p class="lesson-name">{{ $activeLesson->title }}</p>
                        </div>

                        <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="lesson-chip">
                            <i class="fa-solid fa-arrow-left"></i>
                            {{ __('Course Overview') }}
                        </a>
                    </div>

                    <p class="lesson-description">
                        {{ $activeLesson->description ?: __('Title + description + time post + like + save list + share section prepared for lesson detail page.') }}
                    </p>

                    <div class="lesson-meta-row">
                        <span><i class="fa-regular fa-clock"></i> {{ gmdate('i:s', (int) ($activeLesson->duration_seconds ?: 0)) }}</span>
                        <span><i class="fa-solid fa-book-open"></i> {{ __('Lesson') }} {{ $course->lessons->search(fn ($lesson) => $lesson->id === $activeLesson->id) + 1 }}</span>
                        <span><i class="fa-solid fa-eye"></i> {{ $activeLesson->is_preview ? __('Preview enabled') : __('Full lesson') }}</span>
                    </div>

                    <div class="lesson-chip-row">
                        @auth
                            <form action="{{ route('courses.like', $courseRouteKey) }}" method="POST" class="lesson-chip-form">
                                @csrf
                                <button type="submit" class="lesson-chip {{ $isLiked ? 'is-active' : '' }}">
                                    <i class="{{ $isLiked ? 'fa-solid' : 'fa-regular' }} fa-heart"></i> {{ __('Like') }}
                                </button>
                            </form>

                            <form action="{{ route('courses.save', $courseRouteKey) }}" method="POST" class="lesson-chip-form">
                                @csrf
                                <button type="submit" class="lesson-chip {{ $isSaved ? 'is-active' : '' }}">
                                    <i class="{{ $isSaved ? 'fa-solid' : 'fa-regular' }} fa-bookmark"></i> {{ __('Save List') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('web.login', ['redirect' => route('learning.show', [$courseRouteKey, $lessonRouteKey])]) }}" class="lesson-chip">
                                <i class="fa-regular fa-heart"></i> {{ __('Like') }}
                            </a>

                            <a href="{{ route('web.login', ['redirect' => route('learning.show', [$courseRouteKey, $lessonRouteKey])]) }}" class="lesson-chip">
                                <i class="fa-regular fa-bookmark"></i> {{ __('Save List') }}
                            </a>
                        @endauth

                        <button type="button" class="lesson-chip" data-share-url="{{ route('learning.show', [$courseRouteKey, $lessonRouteKey]) }}">
                            <i class="fa-solid fa-share-nodes"></i> {{ __('Share') }}
                        </button>
                    </div>
                </div>

                <div class="lesson-box lesson-comment-box">
                    <h2 class="lesson-section-title">{{ __('Users Comment') }}</h2>
                    @auth
                        <form action="{{ route('learning.comments.store', [$courseRouteKey, $lessonRouteKey]) }}" method="POST" class="lesson-comment-form">
                            @csrf
                            <textarea class="lesson-comment-area" name="comment" placeholder="{{ __('Write comment about this lesson...') }}" required>{{ old('comment') }}</textarea>
                            <button type="submit" class="lesson-comment-send" aria-label="Send comment">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    @else
                        <div class="lesson-comment-empty">
                            {{ __('Please login first to comment on this lesson.') }}
                            <a href="{{ route('web.login', ['redirect' => route('learning.show', [$courseRouteKey, $lessonRouteKey])]) }}">{{ __('Login') }}</a>
                        </div>
                    @endauth

                    @if ($lessonComments->isEmpty())
                        <div class="lesson-comment-empty">{{ __('No lesson comments yet. Be the first one to share your feedback.') }}</div>
                    @else
                        <div class="lesson-comment-list">
                            @foreach ($lessonComments as $comment)
                                <article class="lesson-comment-item">
                                    <div class="lesson-comment-item__head">
                                        <div class="lesson-comment-item__identity">
                                            <span class="lesson-comment-item__avatar">
                                                @php
                                                    $lessonCommentUserName = $comment->user?->name ?: __('User');
                                                    $lessonCommentAvatar = $comment->user?->avatar_url
                                                        ?: 'https://ui-avatars.com/api/?name=' . rawurlencode($lessonCommentUserName) . '&background=dbeafe&color=1d4ed8&bold=true';
                                                @endphp
                                                <img src="{{ $lessonCommentAvatar }}" alt="{{ $lessonCommentUserName }}">
                                            </span>
                                            <div class="lesson-comment-item__meta">
                                                <strong>{{ $lessonCommentUserName }}</strong>
                                                <span>{{ optional($comment->created_at)->format('d M Y h:i A') ?: '-' }}</span>
                                            </div>
                                        </div>

                                        @if (auth()->id() === $comment->user_id)
                                            <div class="lesson-comment-item__actions">
                                                <button type="button" class="lesson-comment-action-btn" data-lesson-comment-edit-toggle="{{ $comment->id }}">{{ __('Edit') }}</button>
                                                <form action="{{ route('learning.comments.destroy', [$courseRouteKey, $lessonRouteKey, $comment]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="lesson-comment-action-btn is-danger">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <p>{{ $comment->comment }}</p>

                                    @if (auth()->id() === $comment->user_id)
                                        <form action="{{ route('learning.comments.update', [$courseRouteKey, $lessonRouteKey, $comment]) }}" method="POST" class="lesson-comment-edit-form" id="lesson-comment-edit-{{ $comment->id }}" hidden>
                                            @csrf
                                            @method('PUT')
                                            <textarea name="comment" required>{{ $comment->comment }}</textarea>
                                            <div class="lesson-comment-edit-form__actions">
                                                <button type="button" class="lesson-comment-action-btn" data-lesson-comment-edit-cancel="{{ $comment->id }}">{{ __('Cancel') }}</button>
                                                <button type="submit" class="lesson-comment-action-btn">{{ __('Update') }}</button>
                                            </div>
                                        </form>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="lesson-box lesson-resource-box">
                    <h2 class="lesson-section-title">{{ __('Lesson Resources') }}</h2>

                    @if ($visibleResources->isEmpty())
                        <div class="lesson-comment-empty">{{ __('No PDF resources uploaded for this lesson yet.') }}</div>
                    @else
                        <div class="lesson-resource-list">
                            @foreach ($visibleResources as $resource)
                                @php
                                    $resourceLabel = $resource->lesson?->title
                                        ? __('Lesson') . ': ' . $resource->lesson->title
                                        : __('Course Overview');
                                @endphp
                                <div class="lesson-resource-item">
                                    <div class="lesson-resource-item__main">
                                        <span class="lesson-resource-item__icon">
                                            <i class="fa-regular fa-file-pdf"></i>
                                        </span>
                                        <div class="lesson-resource-item__meta">
                                            <strong>{{ $resource->title }}</strong>
                                            <span>{{ $resourceLabel }}</span>
                                        </div>
                                    </div>

                                    <div class="lesson-resource-item__actions">
                                        <span class="lesson-resource-badge">{{ $resource->is_free ? __('Free') : __('Paid') }}</span>
                                        @if ($resource->file_url)
                                            <a href="{{ $resource->file_url }}" target="_blank" class="lesson-resource-open-btn">
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

            <aside class="lesson-sidebar-box">
                <div class="lesson-sidebar-head">
                    <h3>{{ $course->title }}</h3>
                    <span>{{ __('Sort Lesson') }}</span>
                </div>

                <div class="lesson-list-shell">
                    @foreach ($course->lessons as $lesson)
                        @php
                            $lessonLocked = ($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) && !$lesson->is_preview;
                            $lessonRoute = $lessonLocked
                                ? (auth()->check() ? $checkoutRoute : $loginRedirectRoute)
                                : route('learning.show', [$course->slug ?: $course->id, $lesson->slug ?: $lesson->id]);
                        @endphp
                        <a
                            href="{{ $lessonRoute }}"
                            class="lesson-list-row {{ $activeLesson->id === $lesson->id ? 'is-active' : '' }}"
                            data-lesson-link="{{ $lessonRoute }}"
                        >
                            <div class="lesson-list-thumb">
                                @if ($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $lesson->title }}">
                                @endif
                            </div>
                            <div class="lesson-list-meta">
                                <div class="lesson-list-title">{{ $lesson->title }}</div>
                                <p class="lesson-list-copy">{{ \Illuminate\Support\Str::limit(strip_tags((string) $lesson->description), 10) }}</p>
                                <div class="lesson-list-date">{{ optional($lesson->created_at)->format('d M Y') ?: __('New') }}</div>
                            </div>
                            <span class="lesson-list-badge">
                                <span class="lesson-list-badge__mark" aria-hidden="true">•</span>
                                {{ $lesson->is_preview ? __('Free') : (($courseNeedsPayment ?? false) && !($hasCourseAccess ?? false) ? __('Locked') : gmdate('i:s', (int) ($lesson->duration_seconds ?: 0))) }}
                            </span>
                        </a>
                    @endforeach
                </div>
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

            document.querySelectorAll('[data-lesson-link]').forEach((item) => {
                item.addEventListener('click', () => {
                    const url = item.getAttribute('data-lesson-link');

                    if (url && item.getAttribute('href') !== url) {
                        window.location.href = url;
                    }
                });
            });

            document.querySelectorAll('[data-lesson-comment-edit-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-lesson-comment-edit-toggle');
                    document.getElementById(`lesson-comment-edit-${id}`)?.removeAttribute('hidden');
                });
            });

            document.querySelectorAll('[data-lesson-comment-edit-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-lesson-comment-edit-cancel');
                    document.getElementById(`lesson-comment-edit-${id}`)?.setAttribute('hidden', 'hidden');
                });
            });
        });
    </script>
@endpush
