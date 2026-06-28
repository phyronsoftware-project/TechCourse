<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoundToolVoice;
use App\Services\ElevenLabsVoiceService;
use App\Services\GoogleCloudTextToSpeechService;
use App\Services\MediaAudioExtractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;
use Throwable;

class SoundToolController extends Controller
{
    public function __construct(
        protected ElevenLabsVoiceService $elevenLabsVoiceService,
        protected GoogleCloudTextToSpeechService $googleCloudTextToSpeechService,
        protected MediaAudioExtractionService $mediaAudioExtractionService,
    )
    {
    }

    public function index(): View
    {
        return view('admin.pages.tools.sound', [
            'pageTitle' => 'Sound Tool',
            'elevenLabsSummary' => $this->elevenLabsVoiceService->summary(),
            'googleTtsSummary' => $this->googleCloudTextToSpeechService->summary(),
            'audioExtractionSummary' => [
                'is_ready' => $this->mediaAudioExtractionService->isReady(),
                'ffmpeg_path' => $this->mediaAudioExtractionService->ffmpegPath() ?: '-',
            ],
            'savedAudioFiles' => $this->mediaAudioExtractionService->savedAudioFiles(),
            'soundToolVoices' => SoundToolVoice::query()
                ->active()
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function voices(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lang' => ['nullable', 'in:km-KH,en-US'],
        ]);

        try {
            $voiceOptions = SoundToolVoice::query()
                ->active()
                ->when(
                    filled($data['lang'] ?? null),
                    fn ($query) => $query->where(function ($innerQuery) use ($data) {
                        $innerQuery
                            ->whereNull('language_code')
                            ->orWhere('language_code', $data['lang'])
                            ->orWhere('language_code', substr((string) $data['lang'], 0, 2));
                    })
                )
                ->orderByDesc('id')
                ->get()
                ->map(function (SoundToolVoice $voice) {
                    return [
                        'name' => $voice->name,
                        'lang' => $voice->language_code ?: 'multi',
                        'provider' => 'elevenlabs',
                        'providerVoiceId' => $voice->provider_voice_id,
                        'category' => $voice->category,
                        'description' => $voice->description,
                    ];
                })
                ->values()
                ->all();

            $googleVoices = [];

            if ($this->googleCloudTextToSpeechService->summary()['is_ready']) {
                try {
                    $googleVoices = $this->googleCloudTextToSpeechService->listVoices($data['lang'] ?? null);
                } catch (Throwable) {
                    $googleVoices = [];
                }
            }

            return response()->json([
                'ready' => ! empty($voiceOptions) || ! empty($googleVoices),
                'voices' => array_merge(
                    $voiceOptions,
                    array_map(function (array $voice) use ($data) {
                        return [
                            ...$voice,
                            'provider' => 'google',
                            'providerVoiceId' => $voice['name'],
                            'lang' => $voice['languageCodes'][0] ?? ($data['lang'] ?? 'en-US'),
                        ];
                    }, $googleVoices),
                ),
            ]);
        } catch (Throwable $throwable) {
            return response()->json([
                'ready' => false,
                'voices' => [],
                'message' => $throwable->getMessage(),
            ], 422);
        }
    }

    public function audio(Request $request): Response
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:500'],
            'lang' => ['required', 'in:km-KH,en-US'],
            'voice' => ['nullable', 'string', 'max:255'],
            'download' => ['nullable', 'in:0,1'],
        ]);

        [$provider, $voiceValue] = $this->parseVoiceSelection($data['voice'] ?? null);
        $audioName = 'tts-' . strtolower(str_replace('-', '_', $data['lang'])) . '-' . now()->format('YmdHis') . '.mp3';

        if ($provider === 'elevenlabs') {
            $audioBinary = $this->elevenLabsVoiceService->synthesize($data['text'], $voiceValue);
        } elseif ($provider === 'browser') {
            throw new \RuntimeException('Browser-only voices cannot be generated or downloaded from the server.');
        } else {
            $audioBinary = $this->googleCloudTextToSpeechService->synthesize(
                $data['text'],
                $data['lang'],
                $provider === 'google' ? $voiceValue : null,
            );
        }

        return response($audioBinary, 200, [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline') . '; filename="' . $audioName . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function extractAudio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'media_file' => ['required', 'file', 'max:102400', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/webm'],
        ]);

        try {
            $result = $this->mediaAudioExtractionService->extractToMp3(
                $request->file('media_file'),
                $data['title'] ?? null,
            );

            return redirect()
                ->route('admin.tools.sound')
                ->with('success', 'Audio extracted successfully: ' . basename($result['path']));
        } catch (Throwable $throwable) {
            return redirect()
                ->route('admin.tools.sound')
                ->with('error', $throwable->getMessage());
        }
    }

    public function cloneVoice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'language_code' => ['nullable', 'in:km-KH,en-US'],
            'description' => ['nullable', 'string', 'max:500'],
            'saved_audio_path' => ['nullable', 'string', 'max:255'],
            'sample_audio_file' => ['nullable', 'file', 'max:51200', 'mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/webm'],
            'remove_background_noise' => ['nullable', 'in:0,1'],
        ]);

        if (! filled($data['saved_audio_path'] ?? null) && ! $request->hasFile('sample_audio_file')) {
            return redirect()
                ->route('admin.tools.sound')
                ->with('error', 'Please choose a saved audio file or upload a new sample audio file first.');
        }

        try {
            $sample = $this->resolveVoiceSample($request, $data);

            $voice = $this->elevenLabsVoiceService->createInstantVoiceClone(
                $data['name'],
                $sample['absolute_path'],
                $sample['file_name'],
                $data['description'] ?? null,
                $request->boolean('remove_background_noise'),
            );

            SoundToolVoice::query()->updateOrCreate(
                [
                    'provider' => 'elevenlabs',
                    'provider_voice_id' => $voice['provider_voice_id'],
                ],
                [
                    'name' => $voice['name'] ?: $data['name'],
                    'language_code' => $data['language_code'] ?: ($voice['language_code'] ?: null),
                    'description' => $data['description'] ?: ($voice['description'] ?: null),
                    'category' => $voice['category'] ?: 'cloned',
                    'sample_audio_path' => $sample['relative_path'] ?? null,
                    'preview_url' => $voice['preview_url'] ?: null,
                    'labels' => $voice['labels'] ?: null,
                    'meta' => $voice['meta'] ?: null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                ],
            );

            return redirect()
                ->route('admin.tools.sound')
                ->with('success', 'Cloned voice created successfully and added to Available Voices.');
        } catch (Throwable $throwable) {
            return redirect()
                ->route('admin.tools.sound')
                ->with('error', $throwable->getMessage());
        }
    }

    protected function parseVoiceSelection(?string $selection): array
    {
        if (! filled($selection) || ! str_contains($selection, '::')) {
            return ['google', $selection];
        }

        [$provider, $voiceValue] = explode('::', $selection, 2);

        return [$provider ?: 'google', $voiceValue];
    }

    protected function resolveVoiceSample(Request $request, array $data): array
    {
        $disk = Storage::disk('public');

        if (filled($data['saved_audio_path'] ?? null)) {
            $savedAudioPath = (string) $data['saved_audio_path'];

            if (! str_starts_with($savedAudioPath, 'resources/sound-tool/extracted/') || ! $disk->exists($savedAudioPath)) {
                throw new \RuntimeException('Selected saved audio file could not be found.');
            }

            return [
                'absolute_path' => $disk->path($savedAudioPath),
                'relative_path' => $savedAudioPath,
                'file_name' => basename($savedAudioPath),
            ];
        }

        $file = $request->file('sample_audio_file');

        if (! $file) {
            throw new \RuntimeException('Voice sample file is missing.');
        }

        $directory = 'resources/sound-tool/voice-samples';
        $disk->makeDirectory($directory);
        $storedPath = $file->storeAs(
            $directory,
            now()->format('YmdHis') . '-' . preg_replace('/[^A-Za-z0-9.\-_]/', '-', $file->getClientOriginalName()),
            'public',
        );

        return [
            'absolute_path' => $disk->path($storedPath),
            'relative_path' => $storedPath,
            'file_name' => basename($storedPath),
        ];
    }
}
