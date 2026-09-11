<?php

namespace Tests\Unit;

use App\Support\VideoPlayback;
use Tests\TestCase;

class VideoPlaybackTest extends TestCase
{
    // Resolve local lesson uploads through the existing public media endpoint.
    public function test_it_builds_uploaded_lesson_video_url(): void
    {
        $this->assertSame(
            url('/media/public/lesson-videos/java-basic.mp4'),
            VideoPlayback::uploadedUrl('lesson-videos/java-basic.mp4')
        );
    }

    // Keep the video ID and playlist when converting a YouTube watch URL.
    public function test_it_builds_youtube_embed_url_with_playlist(): void
    {
        $url = 'https://www.youtube.com/watch?v=zugAZXZrZKM&list=RDzugAZXZrZKM8';

        $this->assertSame(
            'https://www.youtube.com/embed/zugAZXZrZKM?list=RDzugAZXZrZKM8',
            VideoPlayback::embedUrl($url, 'youtube')
        );
    }

    // Convert a shortened YouTube lesson URL into its player URL.
    public function test_it_builds_embed_url_from_short_youtube_link(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/zugAZXZrZKM',
            VideoPlayback::embedUrl('https://youtu.be/zugAZXZrZKM', 'youtube')
        );
    }

    // Convert a Vimeo lesson URL into its player URL.
    public function test_it_builds_vimeo_embed_url(): void
    {
        $this->assertSame(
            'https://player.vimeo.com/video/123456789',
            VideoPlayback::embedUrl('https://vimeo.com/123456789', 'vimeo')
        );
    }
}
