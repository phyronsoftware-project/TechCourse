@extends('admin.layouts.app')

@section('title', 'Sound Tool')

@section('content')
    <style>
        .sound-tool-shell {
            display: grid;
            gap: 16px;
        }

        .sound-tool-hero,
        .sound-tool-card,
        .sound-tool-status {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe5f0;
            border-radius: 28px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        }

        .sound-tool-hero {
            padding: 24px;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: 18px;
            align-items: center;
        }

        .sound-tool-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0b84a5;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .sound-tool-hero h1,
        .sound-tool-card h2 {
            margin: 14px 0 8px;
            color: #172b4d;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .sound-tool-hero p,
        .sound-tool-card p,
        .sound-tool-field label,
        .sound-tool-note,
        .sound-tool-status p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.75;
        }

        .sound-tool-hero__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .sound-tool-hero__metric {
            padding: 14px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #dbe5f0;
        }

        .sound-tool-hero__metric strong {
            display: block;
            color: #173f87;
            font-size: 1.1rem;
        }

        .sound-tool-layout {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .sound-tool-card {
            padding: 22px;
        }

        .sound-tool-toolbar,
        .sound-tool-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sound-tool-toolbar {
            margin-top: 16px;
        }

        .sound-tool-field {
            display: grid;
            gap: 8px;
            margin-top: 16px;
        }

        .sound-tool-select,
        .sound-tool-textarea {
            width: 100%;
            border: 1px solid #d6e0eb;
            background: #fff;
            color: #334155;
            border-radius: 18px;
            min-height: 48px;
            padding: 12px 14px;
            font-size: 14px;
        }

        .sound-tool-textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.75;
        }

        .sound-tool-btn {
            min-height: 44px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .sound-tool-btn-primary {
            background: #173f87;
            border-color: #173f87;
            color: #fff;
        }

        .sound-tool-btn-primary:hover {
            background: #12356f;
        }

        .sound-tool-btn-accent {
            background: #0b84a5;
            border-color: #0b84a5;
            color: #fff;
        }

        .sound-tool-btn-secondary {
            background: #fff;
            border-color: #d6e0eb;
            color: #52657f;
        }

        .sound-tool-status {
            padding: 18px 20px;
        }

        .sound-tool-status strong {
            display: block;
            color: #172b4d;
            font-size: 1rem;
            font-weight: 800;
        }

        .sound-tool-status__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: #eef5fd;
            color: #173f87;
            font-size: 12px;
            font-weight: 800;
        }

        @media (max-width: 980px) {
            .sound-tool-hero,
            .sound-tool-layout {
                grid-template-columns: 1fr;
            }

            .sound-tool-hero__grid {
                grid-template-columns: 1fr;
            }
        }

        .sound-tool-upload-list {
            display: grid;
            gap: 12px;
        }

        .sound-tool-upload-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid #dbe5f0;
            background: #fff;
        }

        .sound-tool-upload-item strong {
            display: block;
            color: #172b4d;
            font-size: 14px;
        }

        .sound-tool-upload-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>

    <div class="sound-tool-shell">
        <section class="sound-tool-hero">
            <div>
                <span class="sound-tool-hero__eyebrow">Business Tool</span>
                <h1>Khmer / English Sound Tool</h1>
                <p>This tool is separate from TechCourse management features. Use it for your other business workflow when you need Khmer or English text-to-speech and speech-to-text inside the dashboard.</p>
            </div>

            <div class="sound-tool-hero__grid">
                <div class="sound-tool-hero__metric">
                    <strong>2 Modes</strong>
                    <span>Text to Speech + Speech to Text</span>
                </div>
                <div class="sound-tool-hero__metric">
                    <strong>2 Languages</strong>
                    <span>Khmer and English</span>
                </div>
                <div class="sound-tool-hero__metric">
                    <strong>Browser Based</strong>
                    <span>No extra server setup needed</span>
                </div>
            </div>
        </section>

        <section class="sound-tool-status">
            <div class="sound-tool-inline">
                <span class="sound-tool-status__badge">ElevenLabs Voice Clone: {{ $elevenLabsSummary['is_ready'] ? 'ready' : 'not ready' }}</span>
                <span class="sound-tool-status__badge">Model: {{ $elevenLabsSummary['model_id'] }}</span>
                <span class="sound-tool-status__badge">Key: {{ $elevenLabsSummary['api_key_masked'] }}</span>
            </div>
            @unless ($elevenLabsSummary['is_ready'])
                <p class="mt-3">Set `ELEVENLABS_API_KEY` first if you want uploaded voice samples to appear in Available Voices and speak typed text like that sample.</p>
            @endunless
        </section>

        <section class="sound-tool-status">
            <div class="sound-tool-inline">
                <span class="sound-tool-status__badge">Google Cloud TTS: {{ $googleTtsSummary['is_ready'] ? 'ready' : 'not ready' }}</span>
                <span class="sound-tool-status__badge">Project: {{ $googleTtsSummary['project_id'] }}</span>
                <span class="sound-tool-status__badge">Client: {{ $googleTtsSummary['client_email_masked'] }}</span>
            </div>
            @unless ($googleTtsSummary['is_ready'])
                <p class="mt-3">Set `GOOGLE_CLOUD_TTS_SERVICE_ACCOUNT_JSON_PATH` or `GOOGLE_CLOUD_TTS_SERVICE_ACCOUNT_JSON` first, then refresh this page to use official Google Cloud voices.</p>
            @endunless
        </section>

        <section class="sound-tool-status">
            <div class="sound-tool-inline">
                <span class="sound-tool-status__badge">Audio Extractor: {{ $audioExtractionSummary['is_ready'] ? 'ready' : 'not ready' }}</span>
                <span class="sound-tool-status__badge">FFmpeg: {{ $audioExtractionSummary['ffmpeg_path'] }}</span>
            </div>
            @unless ($audioExtractionSummary['is_ready'])
                <p class="mt-3">FFmpeg is required for upload video/audio -> extract MP3 flow. Rebuild Docker after updating the image packages.</p>
            @endunless
        </section>

        <section class="sound-tool-layout">
            <article class="sound-tool-card">
                <h2>Text To Sound</h2>
                <p>Write Khmer or English text, select a voice, then play or download sound. You can keep using browser/cloud voices like before, and cloned voices are only an extra option.</p>

                <div class="sound-tool-field">
                    <label for="tts-language">Language</label>
                    <select id="tts-language" class="sound-tool-select">
                        <option value="km-KH">Khmer</option>
                        <option value="en-US">English</option>
                    </select>
                </div>

                <div class="sound-tool-field">
                    <label for="tts-voice">Available Voices</label>
                    <select id="tts-voice" class="sound-tool-select">
                        <option value="">Loading voices...</option>
                    </select>
                </div>

                <div class="sound-tool-field">
                    <label for="tts-text">Input text</label>
                    <textarea id="tts-text" class="sound-tool-textarea" placeholder="Type Khmer or English text here..."></textarea>
                </div>

                <div class="sound-tool-toolbar">
                    <button type="button" class="sound-tool-btn sound-tool-btn-primary" data-sound-play>Play Sound</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-accent" data-sound-download>Download Audio</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-stop>Stop</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-clear-tts>Clear</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-copy-tts>Copy Text</button>
                </div>

                <p class="sound-tool-note">Tip: `Browser voices` are always available first for normal use like before. `ElevenLabs cloned voices` and `Google Cloud voices` are extra options when ready.</p>
                <audio controls class="mt-4 w-full" data-sound-audio-player preload="none"></audio>
            </article>

            <article class="sound-tool-card">
                <h2>Sound To Text</h2>
                <p>Use the microphone to speak Khmer or English and convert the speech into text in this dashboard page.</p>

                <div class="sound-tool-field">
                    <label for="stt-language">Language</label>
                    <select id="stt-language" class="sound-tool-select">
                        <option value="km-KH">Khmer</option>
                        <option value="en-US">English</option>
                    </select>
                </div>

                <div class="sound-tool-field">
                    <label for="stt-text">Recognized text</label>
                    <textarea id="stt-text" class="sound-tool-textarea" placeholder="Your speech result will appear here..."></textarea>
                </div>

                <div class="sound-tool-toolbar">
                    <button type="button" class="sound-tool-btn sound-tool-btn-accent" data-sound-record>Start Record</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-stop-record>Stop Record</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-clear-stt>Clear</button>
                    <button type="button" class="sound-tool-btn sound-tool-btn-secondary" data-sound-copy-stt>Copy Text</button>
                </div>

                <p class="sound-tool-note">Tip: Speech recognition support is usually available in Chrome / Edge. Some browsers may not support microphone speech recognition for Khmer.</p>
            </article>
        </section>

        <section class="sound-tool-card">
            <h2>Create Voice From Uploaded Sound</h2>
            <p>Use a saved extracted MP3 or upload a clean audio sample, then create an `Instant Voice Clone` with ElevenLabs. After success, it will appear in `Available Voices` for text to speech.</p>
            <p class="sound-tool-note">If your ElevenLabs plan does not support voice cloning, you can skip this section and continue using the normal `Play Sound` flow above.</p>

            <form action="{{ route('admin.tools.sound.clone-voice') }}" method="POST" enctype="multipart/form-data" class="sound-tool-field">
                @csrf

                <div class="sound-tool-field">
                    <label for="clone-name">Voice name</label>
                    <input id="clone-name" type="text" name="name" class="sound-tool-select" value="{{ old('name') }}" placeholder="Example: Khmer Female Social Voice" required>
                </div>

                <div class="sound-tool-field">
                    <label for="clone-language">Language</label>
                    <select id="clone-language" name="language_code" class="sound-tool-select">
                        <option value="">Auto / Not set</option>
                        <option value="km-KH" @selected(old('language_code') === 'km-KH')>Khmer</option>
                        <option value="en-US" @selected(old('language_code') === 'en-US')>English</option>
                    </select>
                </div>

                <div class="sound-tool-field">
                    <label for="clone-description">Description</label>
                    <textarea id="clone-description" name="description" class="sound-tool-textarea" style="min-height: 120px;" placeholder="Optional note about this voice sample">{{ old('description') }}</textarea>
                </div>

                <div class="sound-tool-field">
                    <label for="clone-saved-audio">Use saved extracted MP3</label>
                    <select id="clone-saved-audio" name="saved_audio_path" class="sound-tool-select">
                        <option value="">Choose from Saved Audio Library</option>
                        @foreach ($savedAudioFiles as $audioFile)
                            <option value="{{ $audioFile['path'] }}" @selected(old('saved_audio_path') === $audioFile['path'])>{{ $audioFile['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sound-tool-field">
                    <label for="clone-audio-file">Or upload new sample audio</label>
                    <input id="clone-audio-file" type="file" name="sample_audio_file" class="sound-tool-select" accept="audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/webm">
                </div>

                <div class="sound-tool-field">
                    <label class="sound-tool-inline" style="align-items: center;">
                        <input type="checkbox" name="remove_background_noise" value="1" @checked(old('remove_background_noise'))>
                        <span>Remove background noise before cloning</span>
                    </label>
                </div>

                <div class="sound-tool-toolbar">
                    <button type="submit" class="sound-tool-btn sound-tool-btn-primary">Create Cloned Voice</button>
                </div>
            </form>
        </section>

        <section class="sound-tool-card">
            <h2>Upload Video / Audio And Save Sound</h2>
            <p>Upload a video or audio file you already downloaded from social media, extract the sound to MP3, save it in this dashboard, then play or download it later anytime.</p>
            <p class="sound-tool-note">Current recommended max upload after rebuild: 128MB per file.</p>

            <form action="{{ route('admin.tools.sound.extract-audio') }}" method="POST" enctype="multipart/form-data" class="sound-tool-field">
                @csrf
                <div class="sound-tool-field">
                    <label for="media-title">Custom title</label>
                    <input id="media-title" type="text" name="title" class="sound-tool-select" value="{{ old('title') }}" placeholder="Optional title for saved sound">
                </div>

                <div class="sound-tool-field">
                    <label for="media-file">Upload video or audio file</label>
                    <input id="media-file" type="file" name="media_file" class="sound-tool-select" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/webm" required>
                </div>

                <div class="sound-tool-toolbar">
                    <button type="submit" class="sound-tool-btn sound-tool-btn-primary">Extract And Save MP3</button>
                </div>
            </form>
        </section>

        <section class="sound-tool-card">
            <h2>Saved Cloned Voices</h2>
            <p>These cloned voices are saved in your project and are the first options shown in `Available Voices`.</p>

            @if ($soundToolVoices->count())
                <div class="sound-tool-upload-list">
                    @foreach ($soundToolVoices as $voice)
                        <article class="sound-tool-upload-item">
                            <div>
                                <strong>{{ $voice->name }}</strong>
                                <p>{{ $voice->provider }} | {{ $voice->language_code ?: 'multi' }} | {{ $voice->category }}</p>
                                @if ($voice->description)
                                    <p>{{ $voice->description }}</p>
                                @endif
                                @if ($voice->preview_url)
                                    <audio controls class="mt-3 w-full" preload="none">
                                        <source src="{{ $voice->preview_url }}" type="audio/mpeg">
                                    </audio>
                                @endif
                            </div>

                            <div class="sound-tool-upload-actions">
                                <span class="sound-tool-btn sound-tool-btn-secondary">{{ $voice->provider_voice_id }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="sound-tool-note">No cloned voices yet. Create one from a saved MP3 or upload a clean voice sample above.</p>
            @endif
        </section>

        <section class="sound-tool-card">
            <h2>Saved Audio Library</h2>
            <p>These are the extracted MP3 files saved in your project. You can play them later or download them again whenever you want.</p>

            @if (count($savedAudioFiles))
                <div class="sound-tool-upload-list">
                    @foreach ($savedAudioFiles as $audioFile)
                        <article class="sound-tool-upload-item">
                            <div>
                                <strong>{{ $audioFile['name'] }}</strong>
                                <p>{{ number_format(($audioFile['size'] ?? 0) / 1024 / 1024, 2) }} MB | {{ \Carbon\Carbon::createFromTimestamp($audioFile['last_modified'])->format('d M Y h:i A') }}</p>
                                <audio controls class="mt-3 w-full" preload="none">
                                    <source src="{{ $audioFile['url'] }}" type="audio/mpeg">
                                </audio>
                            </div>

                            <div class="sound-tool-upload-actions">
                                <a href="{{ $audioFile['url'] }}" target="_blank" class="sound-tool-btn sound-tool-btn-secondary">Open</a>
                                <a href="{{ $audioFile['url'] }}" download class="sound-tool-btn sound-tool-btn-accent">Download</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="sound-tool-note">No saved MP3 files yet. Upload a social video/audio file above to create your first saved sound.</p>
            @endif
        </section>

        <section class="sound-tool-status">
            <div class="sound-tool-inline">
                <span class="sound-tool-status__badge" data-sound-tts-status>Text to speech: idle</span>
                <span class="sound-tool-status__badge" data-sound-stt-status>Speech to text: idle</span>
                <span class="sound-tool-status__badge" data-sound-support-status>Checking browser support...</span>
            </div>
            <p id="sound-tool-status-copy" class="mt-3">This sound tool runs in the browser. Microphone permission and available system voices depend on the current device and browser.</p>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ttsLanguage = document.getElementById('tts-language');
            const ttsVoice = document.getElementById('tts-voice');
            const ttsText = document.getElementById('tts-text');
            const sttLanguage = document.getElementById('stt-language');
            const sttText = document.getElementById('stt-text');
            const audioPlayer = document.querySelector('[data-sound-audio-player]');
            const ttsStatus = document.querySelector('[data-sound-tts-status]');
            const sttStatus = document.querySelector('[data-sound-stt-status]');
            const supportStatus = document.querySelector('[data-sound-support-status]');
            const statusCopy = document.getElementById('sound-tool-status-copy');
            const audioBaseUrl = @json(route('admin.tools.sound.audio'));
            const voicesUrl = @json(route('admin.tools.sound.voices'));
            const elevenLabsReady = @json((bool) $elevenLabsSummary['is_ready']);
            const googleTtsReady = @json((bool) $googleTtsSummary['is_ready']);

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
            let recognition = null;
            let availableVoices = [];

            const updateBadge = (node, text) => {
                if (node) {
                    node.textContent = text;
                }
            };

            const parseVoiceValue = (value) => {
                if (!value || !value.includes('::')) {
                    return {
                        provider: googleTtsReady ? 'google' : 'browser',
                        providerVoiceId: value || '',
                    };
                }

                const [provider, providerVoiceId] = value.split('::', 2);

                return {
                    provider,
                    providerVoiceId,
                };
            };

            const copyToClipboard = async (value) => {
                if (!value) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(value);
                    statusCopy.textContent = 'Copied text to clipboard successfully.';
                } catch (error) {
                    statusCopy.textContent = 'Clipboard copy failed in this browser.';
                }
            };

            const chooseVoice = (lang) => {
                const selectedVoice = availableVoices.find((voice) => voice.name === ttsVoice?.value);
                const selectedByOptionValue = availableVoices.find((voice) => voice.optionValue === ttsVoice?.value);

                if (selectedByOptionValue) {
                    return selectedByOptionValue;
                }

                if (selectedVoice) {
                    return selectedVoice;
                }

                return availableVoices.find((voice) => voice.lang === lang)
                    || availableVoices.find((voice) => voice.lang?.startsWith(lang.split('-')[0]))
                    || availableVoices[0]
                    || null;
            };

            const renderVoiceOptions = () => {
                if (!ttsVoice) {
                    return;
                }

                const lang = ttsLanguage.value;
                const languagePrefix = lang.split('-')[0];
                const matchingVoices = availableVoices.filter((voice) =>
                    voice.lang === lang
                    || voice.lang?.startsWith(languagePrefix)
                    || (Array.isArray(voice.languageCodes) && voice.languageCodes.some((code) => code === lang || code.startsWith(languagePrefix)))
                );
                const filteredVoices = matchingVoices.length ? matchingVoices : availableVoices;

                ttsVoice.innerHTML = '';

                if (!filteredVoices.length) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No browser voice available';
                    ttsVoice.appendChild(option);
                    statusCopy.textContent = 'No browser voice is available on this device. Remote audio can still work.';
                    return;
                }

                filteredVoices.forEach((voice, index) => {
                    const option = document.createElement('option');
                    option.value = voice.optionValue || `${voice.provider || 'google'}::${voice.providerVoiceId || voice.name}`;
                    const voiceLang = voice.lang || voice.languageCodes?.[0] || lang;
                    const gender = voice.ssmlGender ? ` - ${voice.ssmlGender}` : '';
                    const providerLabel = voice.provider === 'elevenlabs'
                        ? ' - cloned'
                        : (voice.provider === 'browser' ? ' - browser' : ' - cloud');
                    option.textContent = `${voice.name} (${voiceLang})${gender}${providerLabel}${voice.default ? ' - default' : ''}`;
                    if (index === 0) {
                        option.selected = true;
                    }
                    ttsVoice.appendChild(option);
                });

                if (!matchingVoices.length) {
                    statusCopy.textContent = 'No exact Khmer voice was found, so the tool is showing other available browser voices as fallback.';
                }
            };

            const getBrowserVoices = () => {
                return (window.speechSynthesis?.getVoices?.() ?? []).map((voice) => ({
                    name: voice.name,
                    lang: voice.lang,
                    default: voice.default,
                    provider: 'browser',
                    providerVoiceId: voice.name,
                    optionValue: `browser::${voice.name}`,
                }));
            };

            const mergeVoices = (...voiceGroups) => {
                const merged = [];
                const seen = new Set();

                voiceGroups.flat().forEach((voice) => {
                    const key = `${voice.provider || 'unknown'}::${voice.providerVoiceId || voice.name || ''}`;

                    if (!key || seen.has(key)) {
                        return;
                    }

                    seen.add(key);
                    merged.push(voice);
                });

                return merged;
            };

            const loadBrowserVoices = () => {
                availableVoices = getBrowserVoices();
                renderVoiceOptions();
            };

            const loadRemoteVoices = async () => {
                try {
                    const response = await fetch(`${voicesUrl}?lang=${encodeURIComponent(ttsLanguage.value)}`, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json();

                    if (!response.ok || !Array.isArray(payload.voices) || !payload.voices.length) {
                        throw new Error(payload.message || 'Google Cloud voices not available.');
                    }

                    const remoteVoices = payload.voices.map((voice) => ({
                        ...voice,
                        lang: voice.languageCodes?.[0] || ttsLanguage.value,
                        optionValue: `${voice.provider || 'google'}::${voice.providerVoiceId || voice.name}`,
                    }));
                    const browserVoices = getBrowserVoices();
                    availableVoices = mergeVoices(browserVoices, remoteVoices);

                    renderVoiceOptions();
                    statusCopy.textContent = 'Loaded browser voices and remote voice options successfully.';
                } catch (error) {
                    loadBrowserVoices();
                    statusCopy.textContent = 'Remote voices could not be loaded, so browser voices are being used as fallback.';
                }
            };

            const loadVoices = () => {
                if (googleTtsReady || elevenLabsReady) {
                    loadRemoteVoices();
                    return;
                }

                loadBrowserVoices();
            };

            const detectSupport = () => {
                const ttsSupported = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;
                const sttSupported = !!SpeechRecognition;

                updateBadge(supportStatus, `Browser support: TTS ${ttsSupported ? 'yes' : 'no'} / STT ${sttSupported ? 'yes' : 'no'}`);

                if (!ttsSupported || !sttSupported) {
                    statusCopy.textContent = 'Some browsers do not support every speech feature. Chrome or Edge is recommended for best result.';
                }
            };

            const buildAudioUrl = (download = false) => {
                const value = ttsText.value.trim();
                const voiceSelection = parseVoiceValue(ttsVoice?.value || '');

                if (!value) {
                    statusCopy.textContent = 'Please enter text before generating audio.';
                    return null;
                }

                if (!download && voiceSelection.provider === 'browser' && !googleTtsReady) {
                    return null;
                }

                if (download && voiceSelection.provider === 'browser') {
                    statusCopy.textContent = 'Browser voices cannot be downloaded. Please select a cloned voice or cloud voice.';
                    return null;
                }

                const params = new URLSearchParams({
                    text: value,
                    lang: ttsLanguage.value,
                    voice: ttsVoice?.value || '',
                    download: download ? '1' : '0',
                });

                return `${audioBaseUrl}?${params.toString()}`;
            };

            const playRemoteAudio = async () => {
                const audioUrl = buildAudioUrl(false);

                if (!audioUrl || !audioPlayer) {
                    return false;
                }

                try {
                    audioPlayer.src = audioUrl;
                    audioPlayer.load();
                    await audioPlayer.play();
                    updateBadge(ttsStatus, 'Text to speech: playing audio');
                    statusCopy.textContent = 'Playing generated audio file.';
                    return true;
                } catch (error) {
                    return false;
                }
            };

            const playSpeech = () => {
                const value = ttsText.value.trim();

                if (!value) {
                    statusCopy.textContent = 'Please enter text before playing sound.';
                    return;
                }

                playRemoteAudio().then((played) => {
                    if (played) {
                        return;
                    }

                    if (!('speechSynthesis' in window) || !('SpeechSynthesisUtterance' in window)) {
                        statusCopy.textContent = 'This browser does not support text to speech.';
                        return;
                    }

                    window.speechSynthesis.cancel();

                    const utterance = new SpeechSynthesisUtterance(value);
                    const lang = ttsLanguage.value;
                    const chosenVoice = chooseVoice(lang);
                    utterance.lang = lang;
                    utterance.voice = chosenVoice?.provider === 'browser' ? chosenVoice : null;
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    utterance.volume = 1;

                    utterance.onstart = () => updateBadge(ttsStatus, 'Text to speech: browser voice');
                    utterance.onend = () => {
                        updateBadge(ttsStatus, 'Text to speech: finished');
                        statusCopy.textContent = 'Browser voice finished. If you still do not hear Khmer, your device may not have Khmer voice installed.';
                    };
                    utterance.onerror = () => {
                        updateBadge(ttsStatus, 'Text to speech: error');
                        statusCopy.textContent = 'Unable to play sound from remote audio and browser voice engine.';
                    };

                    statusCopy.textContent = 'Google Cloud audio unavailable, trying browser voice fallback.';
                    window.speechSynthesis.speak(utterance);
                });
            };

            const stopSpeech = () => {
                audioPlayer?.pause();
                if (audioPlayer) {
                    audioPlayer.currentTime = 0;
                }

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    updateBadge(ttsStatus, 'Text to speech: stopped');
                }
            };

            const startRecognition = () => {
                if (!SpeechRecognition) {
                    statusCopy.textContent = 'This browser does not support speech recognition.';
                    updateBadge(sttStatus, 'Speech to text: unsupported');
                    return;
                }

                recognition?.stop();
                recognition = new SpeechRecognition();
                recognition.lang = sttLanguage.value;
                recognition.continuous = true;
                recognition.interimResults = true;

                recognition.onstart = () => {
                    updateBadge(sttStatus, 'Speech to text: listening');
                    statusCopy.textContent = 'Microphone recording started.';
                };

                recognition.onresult = (event) => {
                    let transcript = '';

                    for (let i = 0; i < event.results.length; i += 1) {
                        transcript += event.results[i][0].transcript;
                    }

                    sttText.value = transcript.trim();
                };

                recognition.onerror = (event) => {
                    updateBadge(sttStatus, `Speech to text: ${event.error}`);
                    statusCopy.textContent = 'Speech recognition error happened. Please check microphone permission.';
                };

                recognition.onend = () => {
                    updateBadge(sttStatus, 'Speech to text: idle');
                };

                recognition.start();
            };

            const stopRecognition = () => {
                recognition?.stop();
                updateBadge(sttStatus, 'Speech to text: stopped');
            };

            document.querySelector('[data-sound-play]')?.addEventListener('click', playSpeech);
            document.querySelector('[data-sound-download]')?.addEventListener('click', () => {
                const audioUrl = buildAudioUrl(true);

                if (!audioUrl) {
                    return;
                }

                window.open(audioUrl, '_blank');
                statusCopy.textContent = 'Downloading generated audio file.';
            });
            document.querySelector('[data-sound-stop]')?.addEventListener('click', stopSpeech);
            document.querySelector('[data-sound-record]')?.addEventListener('click', startRecognition);
            document.querySelector('[data-sound-stop-record]')?.addEventListener('click', stopRecognition);
            document.querySelector('[data-sound-clear-tts]')?.addEventListener('click', () => {
                ttsText.value = '';
                updateBadge(ttsStatus, 'Text to speech: idle');
            });
            document.querySelector('[data-sound-clear-stt]')?.addEventListener('click', () => {
                sttText.value = '';
                updateBadge(sttStatus, 'Speech to text: idle');
            });
            document.querySelector('[data-sound-copy-tts]')?.addEventListener('click', () => copyToClipboard(ttsText.value.trim()));
            document.querySelector('[data-sound-copy-stt]')?.addEventListener('click', () => copyToClipboard(sttText.value.trim()));
            ttsLanguage?.addEventListener('change', () => {
                loadVoices();
                updateBadge(ttsStatus, 'Text to speech: idle');
            });

            if ('speechSynthesis' in window) {
                window.speechSynthesis.onvoiceschanged = () => {
                    loadVoices();
                    detectSupport();
                };
            }

            audioPlayer?.addEventListener('ended', () => {
                updateBadge(ttsStatus, 'Text to speech: finished');
            });

            audioPlayer?.addEventListener('error', () => {
                statusCopy.textContent = 'Generated audio could not be played. Trying browser voice is recommended as fallback.';
            });

            loadVoices();
            detectSupport();
        });
    </script>
@endsection
