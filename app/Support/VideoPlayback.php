<?php

namespace App\Support;

class VideoPlayback
{
    // Resolve an uploaded lesson video through the existing public media route.
    public static function uploadedUrl(?string $path): ?string
    {
        $videoPath = trim((string) $path);

        if ($videoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $videoPath)) {
            return $videoPath;
        }

        $videoPath = ltrim($videoPath, '/');

        if (str_starts_with($videoPath, 'storage/')) {
            $videoPath = substr($videoPath, strlen('storage/'));
        }

        return route('media.public', ['path' => $videoPath]);
    }

    // Convert supported lesson links into safe player embed URLs.
    public static function embedUrl(?string $url, ?string $videoType = null): ?string
    {
        $videoUrl = trim((string) $url);

        if ($videoUrl === '') {
            return null;
        }

        $parts = parse_url($videoUrl);

        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', (string) ($parts['host'] ?? '')));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        parse_str((string) ($parts['query'] ?? ''), $query);

        $isYouTube = $videoType === 'youtube'
            || in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com'], true);

        if ($isYouTube) {
            $segments = array_values(array_filter(explode('/', $path)));
            $videoId = null;

            if ($host === 'youtu.be') {
                $videoId = $segments[0] ?? null;
            } elseif (isset($query['v'])) {
                $videoId = (string) $query['v'];
            } elseif (in_array($segments[0] ?? null, ['embed', 'shorts', 'live'], true)) {
                $videoId = $segments[1] ?? null;
            }

            $playlistId = isset($query['list']) ? self::safeKey((string) $query['list']) : null;
            $videoId = self::safeKey((string) $videoId);

            if ($videoId) {
                return 'https://www.youtube.com/embed/'.$videoId.($playlistId ? '?list='.$playlistId : '');
            }

            if ($playlistId) {
                return 'https://www.youtube.com/embed/videoseries?list='.$playlistId;
            }

            return null;
        }

        $isVimeo = $videoType === 'vimeo' || in_array($host, ['vimeo.com', 'player.vimeo.com'], true);

        if ($isVimeo) {
            $segments = array_values(array_filter(explode('/', $path)));
            $videoId = self::safeKey((string) end($segments));

            return $videoId ? 'https://player.vimeo.com/video/'.$videoId : null;
        }

        return null;
    }

    // Allow only provider-safe video and playlist identifiers.
    private static function safeKey(string $value): ?string
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $value) ? $value : null;
    }
}
