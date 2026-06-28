<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ElevenLabsVoiceService
{
    public function summary(): array
    {
        $apiKey = $this->apiKey();

        return [
            'is_ready' => $apiKey !== '',
            'api_key_masked' => $apiKey !== '' ? $this->mask($apiKey, 4, 4) : '-',
            'model_id' => (string) config('services.elevenlabs.model', 'eleven_multilingual_v2'),
        ];
    }

    public function listPersonalVoices(): array
    {
        $response = Http::timeout(30)
            ->withHeaders($this->headers())
            ->acceptJson()
            ->get('https://api.elevenlabs.io/v2/voices', [
                'page_size' => 100,
                'voice_type' => 'personal',
                'include_total_count' => false,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to load ElevenLabs voices right now.');
        }

        return collect($response->json('voices', []))
            ->map(fn (array $voice) => $this->mapVoicePayload($voice))
            ->filter(fn (array $voice) => $voice['provider_voice_id'] !== '')
            ->values()
            ->all();
    }

    public function getVoice(string $voiceId): array
    {
        $response = Http::timeout(30)
            ->withHeaders($this->headers())
            ->acceptJson()
            ->get('https://api.elevenlabs.io/v1/voices/' . urlencode($voiceId));

        if (! $response->successful()) {
            throw new RuntimeException('Unable to load ElevenLabs voice details.');
        }

        return $this->mapVoicePayload($response->json());
    }

    public function createInstantVoiceClone(
        string $name,
        string $sampleAbsolutePath,
        string $sampleFileName,
        ?string $description = null,
        bool $removeBackgroundNoise = false,
    ): array {
        if (! is_file($sampleAbsolutePath)) {
            throw new RuntimeException('Voice sample file could not be found.');
        }

        $response = Http::timeout(120)
            ->withHeaders($this->headers())
            ->attach('files[]', file_get_contents($sampleAbsolutePath), $sampleFileName)
            ->post('https://api.elevenlabs.io/v1/voices/add', array_filter([
                'name' => $name,
                'description' => $description ?: null,
                'remove_background_noise' => $removeBackgroundNoise ? 'true' : 'false',
            ], fn ($value) => $value !== null));

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'detail.message', data_get($response->json(), 'detail', 'Unable to create ElevenLabs cloned voice.'));
            throw new RuntimeException($message);
        }

        $voiceId = (string) $response->json('voice_id', '');

        if ($voiceId === '') {
            throw new RuntimeException('ElevenLabs returned an empty voice ID.');
        }

        return $this->getVoice($voiceId);
    }

    public function synthesize(string $text, string $voiceId): string
    {
        $response = Http::timeout(60)
            ->withHeaders($this->headers())
            ->accept('audio/mpeg')
            ->post('https://api.elevenlabs.io/v1/text-to-speech/' . urlencode($voiceId) . '?output_format=mp3_44100_128', [
                'text' => $text,
                'model_id' => (string) config('services.elevenlabs.model', 'eleven_multilingual_v2'),
            ]);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'detail.message', data_get($response->json(), 'detail', 'Unable to generate ElevenLabs audio.'));
            throw new RuntimeException($message);
        }

        return $response->body();
    }

    protected function mapVoicePayload(array $voice): array
    {
        return [
            'provider' => 'elevenlabs',
            'provider_voice_id' => (string) ($voice['voice_id'] ?? ''),
            'name' => (string) ($voice['name'] ?? ''),
            'language_code' => (string) data_get($voice, 'labels.language', ''),
            'description' => (string) ($voice['description'] ?? ''),
            'category' => (string) ($voice['category'] ?? 'cloned'),
            'preview_url' => (string) ($voice['preview_url'] ?? ''),
            'labels' => is_array($voice['labels'] ?? null) ? $voice['labels'] : [],
            'meta' => $voice,
        ];
    }

    protected function headers(): array
    {
        $apiKey = $this->apiKey();

        if ($apiKey === '') {
            throw new RuntimeException('ElevenLabs API key is not configured.');
        }

        return [
            'xi-api-key' => $apiKey,
        ];
    }

    protected function apiKey(): string
    {
        return trim((string) config('services.elevenlabs.api_key'));
    }

    protected function mask(string $value, int $prefix = 3, int $suffix = 3): string
    {
        if ($value === '') {
            return '-';
        }

        if (strlen($value) <= ($prefix + $suffix)) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, $prefix) . str_repeat('*', max(strlen($value) - ($prefix + $suffix), 4)) . substr($value, -$suffix);
    }
}
