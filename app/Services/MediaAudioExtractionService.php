<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaAudioExtractionService
{
    public function isReady(): bool
    {
        return $this->ffmpegPath() !== null;
    }

    public function ffmpegPath(): ?string
    {
        $knownPaths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/bin/ffmpeg',
        ];

        foreach ($knownPaths as $path) {
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        $output = [];
        $exitCode = 1;
        @exec('command -v ffmpeg 2>/dev/null', $output, $exitCode);

        if ($exitCode !== 0 || empty($output[0])) {
            return null;
        }

        return trim($output[0]);
    }

    public function extractToMp3(UploadedFile $file, ?string $label = null): array
    {
        $ffmpeg = $this->ffmpegPath();

        if ($ffmpeg === null) {
            throw new RuntimeException('FFmpeg is not installed on this server/container.');
        }

        $disk = Storage::disk('public');
        $baseDirectory = 'resources/sound-tool';
        $uploadDirectory = $baseDirectory . '/uploads';
        $audioDirectory = $baseDirectory . '/extracted';
        $disk->makeDirectory($uploadDirectory);
        $disk->makeDirectory($audioDirectory);
        $baseName = $this->slug($label ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = now()->format('YmdHis');
        $uploadPath = $file->storeAs($uploadDirectory, $baseName . '-' . $timestamp . '.' . $file->getClientOriginalExtension(), 'public');
        $outputRelativePath = $audioDirectory . '/' . $baseName . '-' . $timestamp . '.mp3';
        $inputAbsolutePath = $disk->path($uploadPath);
        $outputAbsolutePath = $disk->path($outputRelativePath);

        $command = sprintf(
            '%s -y -i %s -vn -acodec libmp3lame -q:a 2 %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputAbsolutePath),
            escapeshellarg($outputAbsolutePath),
        );

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($outputAbsolutePath)) {
            if ($disk->exists($uploadPath)) {
                $disk->delete($uploadPath);
            }

            throw new RuntimeException('Unable to extract audio from the uploaded media file. ' . trim(implode(' ', array_slice($output, -4))));
        }

        $disk->delete($uploadPath);

        return [
            'title' => $baseName,
            'path' => $outputRelativePath,
            'url' => $disk->url($outputRelativePath),
            'size' => $disk->size($outputRelativePath),
            'last_modified' => $disk->lastModified($outputRelativePath),
        ];
    }

    public function savedAudioFiles(): array
    {
        $disk = Storage::disk('public');
        $files = collect($disk->files('resources/sound-tool/extracted'))
            ->filter(fn (string $path) => str_ends_with(strtolower($path), '.mp3'))
            ->map(function (string $path) use ($disk) {
                return [
                    'name' => basename($path),
                    'path' => $path,
                    'url' => $disk->url($path),
                    'size' => $disk->size($path),
                    'last_modified' => $disk->lastModified($path),
                ];
            })
            ->sortByDesc('last_modified')
            ->values()
            ->all();

        return $files;
    }

    protected function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: 'audio-file';

        return trim($value, '-') ?: 'audio-file';
    }
}
