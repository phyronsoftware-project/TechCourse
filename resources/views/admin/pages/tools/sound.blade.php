@extends('admin.layouts.app')

@section('title', 'Voice Tool')

@section('content')
    <style>
        .voice-tool-shell {
            display: grid;
            gap: 18px;
        }

        .voice-tool-heading {
            margin: 0;
            color: #172b4d;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .voice-tool-copy,
        .voice-tool-label,
        .voice-tool-status {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .voice-tool-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .voice-tool-card {
            display: grid;
            align-content: start;
            gap: 16px;
            padding: 24px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        }

        .voice-tool-card h2 {
            margin: 0;
            color: #173f87;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .voice-tool-field {
            display: grid;
            gap: 8px;
        }

        .voice-tool-label {
            font-weight: 700;
        }

        .voice-tool-select,
        .voice-tool-textarea {
            width: 100%;
            border: 1px solid #d6e0eb;
            border-radius: 14px;
            background: #fff;
            color: #334155;
            padding: 12px 14px;
            font-size: 14px;
        }

        .voice-tool-textarea {
            min-height: 190px;
            resize: vertical;
            line-height: 1.75;
        }

        .voice-tool-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .voice-tool-button {
            min-height: 44px;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 13px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
        }

        .voice-tool-button-primary {
            background: #173f87;
            color: #fff;
        }

        .voice-tool-button-accent {
            background: #0b84a5;
            color: #fff;
        }

        .voice-tool-button-secondary {
            border-color: #d6e0eb;
            background: #fff;
            color: #52657f;
        }

        .voice-tool-status {
            min-height: 28px;
            margin: 0;
        }

        @media (max-width: 900px) {
            .voice-tool-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="voice-tool-shell">
        <div>
            <h1 class="voice-tool-heading">Voice Tool</h1>
            <p class="voice-tool-copy">និយាយដើម្បីបានជា Text ឬបញ្ចូល Text ដើម្បីបង្កើតជា Voice។</p>
        </div>

        <div class="voice-tool-grid">
            <section class="voice-tool-card">
                <div>
                    <h2>Voice to Text</h2>
                    <p class="voice-tool-copy">ចុច Start ហើយនិយាយ។ Text ដែលស្គាល់បាននឹងបង្ហាញនៅខាងក្រោម។</p>
                </div>

                <div class="voice-tool-field">
                    <label class="voice-tool-label" for="stt-language">Language</label>
                    <select id="stt-language" class="voice-tool-select">
                        <option value="km-KH">Khmer</option>
                        <option value="en-US">English</option>
                    </select>
                </div>

                <div class="voice-tool-field">
                    <label class="voice-tool-label" for="stt-text">Recognized Text</label>
                    <textarea id="stt-text" class="voice-tool-textarea" placeholder="Your voice text will appear here..."></textarea>
                </div>

                <div class="voice-tool-actions">
                    <button type="button" class="voice-tool-button voice-tool-button-primary" data-sound-record>Start Voice</button>
                    <button type="button" class="voice-tool-button voice-tool-button-secondary" data-sound-stop-record>Stop</button>
                    <button type="button" class="voice-tool-button voice-tool-button-secondary" data-sound-clear-stt>Clear</button>
                </div>

                <p class="voice-tool-status" data-sound-stt-status>Speech to text: idle</p>
            </section>

            <section class="voice-tool-card">
                <div>
                    <h2>Text to Voice</h2>
                    <p class="voice-tool-copy">បញ្ចូល text, ជ្រើស voice រួច Play ឬ Download ជា audio file។</p>
                </div>

                <div class="voice-tool-field">
                    <label class="voice-tool-label" for="tts-language">Language</label>
                    <select id="tts-language" class="voice-tool-select">
                        <option value="km-KH">Khmer</option>
                        <option value="en-US">English</option>
                    </select>
                </div>

                <div class="voice-tool-field">
                    <label class="voice-tool-label" for="tts-voice">Voice</label>
                    <select id="tts-voice" class="voice-tool-select">
                        <option value="">Loading voices...</option>
                    </select>
                </div>

                <div class="voice-tool-field">
                    <label class="voice-tool-label" for="tts-text">Text</label>
                    <textarea id="tts-text" class="voice-tool-textarea" placeholder="Type Khmer or English text here..."></textarea>
                </div>

                <div class="voice-tool-actions">
                    <button type="button" class="voice-tool-button voice-tool-button-primary" data-sound-play>Play Voice</button>
                    <button type="button" class="voice-tool-button voice-tool-button-accent" data-sound-download>Download Voice</button>
                    <button type="button" class="voice-tool-button voice-tool-button-secondary" data-sound-stop>Stop</button>
                    <button type="button" class="voice-tool-button voice-tool-button-secondary" data-sound-clear-tts>Clear</button>
                </div>

                <audio controls class="w-full" data-sound-audio-player preload="none"></audio>
                <p class="voice-tool-status" data-sound-tts-status>Text to speech: idle</p>
            </section>
        </div>

        <p id="sound-tool-status-copy" class="voice-tool-status">Chrome or Edge is recommended for microphone speech recognition.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ttsLanguage = document.getElementById('tts-language');
            const ttsVoice = document.getElementById('tts-voice');
            const ttsText = document.getElementById('tts-text');
            const sttLanguage = document.getElementById('stt-language');
            const sttText = document.getElementById('stt-text');
            const recordButton = document.querySelector('[data-sound-record]');
            const audioPlayer = document.querySelector('[data-sound-audio-player]');
            const ttsStatus = document.querySelector('[data-sound-tts-status]');
            const sttStatus = document.querySelector('[data-sound-stt-status]');
            const statusCopy = document.getElementById('sound-tool-status-copy');
            const audioBaseUrl = @json(route('admin.tools.sound.audio'));
            const voicesUrl = @json(route('admin.tools.sound.voices'));
            const elevenLabsReady = @json((bool) $elevenLabsSummary['is_ready']);
            const googleTtsReady = @json((bool) $googleTtsSummary['is_ready']);
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
            let recognition = null;
            let availableVoices = [];

            // Keep browser voices as a fallback and add cloud/cloned voices when configured.
            const parseVoiceValue = (value) => {
                if (!value || !value.includes('::')) {
                    return { provider: googleTtsReady ? 'google' : 'browser', providerVoiceId: value || '' };
                }

                const [provider, providerVoiceId] = value.split('::', 2);
                return { provider, providerVoiceId };
            };

            const updateStatus = (node, text) => {
                if (node) {
                    node.textContent = text;
                }
            };

            const getBrowserVoices = () => (window.speechSynthesis?.getVoices?.() ?? []).map((voice) => ({
                name: voice.name,
                lang: voice.lang,
                default: voice.default,
                provider: 'browser',
                providerVoiceId: voice.name,
                optionValue: `browser::${voice.name}`,
                browserVoice: voice,
            }));

            const mergeVoices = (...groups) => {
                const merged = [];
                const seen = new Set();

                groups.flat().forEach((voice) => {
                    const key = `${voice.provider || 'unknown'}::${voice.providerVoiceId || voice.name || ''}`;
                    if (seen.has(key)) {
                        return;
                    }

                    seen.add(key);
                    merged.push(voice);
                });

                return merged;
            };

            const renderVoiceOptions = () => {
                const lang = ttsLanguage.value;
                const prefix = lang.split('-')[0];
                const matching = availableVoices.filter((voice) => voice.lang === lang || voice.lang?.startsWith(prefix));
                const voices = matching.length ? matching : availableVoices;

                ttsVoice.innerHTML = '';
                if (!voices.length) {
                    ttsVoice.innerHTML = '<option value="">No voice available</option>';
                    return;
                }

                voices.forEach((voice, index) => {
                    const option = document.createElement('option');
                    option.value = voice.optionValue || `${voice.provider}::${voice.providerVoiceId || voice.name}`;
                    option.textContent = `${voice.name} (${voice.lang || lang})${voice.provider === 'elevenlabs' ? ' - cloned' : ''}`;
                    option.selected = index === 0;
                    ttsVoice.appendChild(option);
                });
            };

            const loadVoices = async () => {
                const browserVoices = getBrowserVoices();
                availableVoices = browserVoices;

                if (googleTtsReady || elevenLabsReady) {
                    try {
                        const response = await fetch(`${voicesUrl}?lang=${encodeURIComponent(ttsLanguage.value)}`, {
                            headers: { Accept: 'application/json' },
                        });
                        const payload = await response.json();
                        const remoteVoices = (payload.voices || []).map((voice) => ({
                            ...voice,
                            optionValue: `${voice.provider}::${voice.providerVoiceId || voice.name}`,
                        }));
                        // Prefer server voices so Khmer text does not silently use a missing browser voice.
                        availableVoices = mergeVoices(remoteVoices, browserVoices);
                    } catch (error) {
                        statusCopy.textContent = 'Cloud voices unavailable. Browser voices are being used.';
                    }
                }

                renderVoiceOptions();
            };

            const chooseBrowserVoice = () => {
                const lang = ttsLanguage.value;
                const selectedVoice = availableVoices.find((voice) => voice.provider === 'browser' && voice.optionValue === ttsVoice.value);
                const matchesLanguage = (voice) => voice?.lang === lang || voice?.lang?.startsWith(lang.split('-')[0]);

                return (matchesLanguage(selectedVoice) ? selectedVoice : null)
                    || availableVoices.find((voice) => voice.provider === 'browser' && voice.lang === lang)
                    || availableVoices.find((voice) => voice.provider === 'browser' && voice.lang?.startsWith(lang.split('-')[0]));
            };

            const buildAudioUrl = (download = false) => {
                const text = ttsText.value.trim();
                const voice = parseVoiceValue(ttsVoice.value);

                if (!text) {
                    statusCopy.textContent = 'Please enter text first.';
                    return null;
                }

                if (voice.provider === 'browser') {
                    statusCopy.textContent = download
                        ? 'Browser voice cannot be downloaded. Please select a cloud or cloned voice.'
                        : 'Playing with browser voice.';
                    return null;
                }

                const params = new URLSearchParams({
                    text,
                    lang: ttsLanguage.value,
                    voice: ttsVoice.value,
                    download: download ? '1' : '0',
                });

                return `${audioBaseUrl}?${params.toString()}`;
            };

            const playVoice = async () => {
                const remoteUrl = buildAudioUrl(false);
                if (remoteUrl) {
                    audioPlayer.src = remoteUrl;
                    audioPlayer.load();
                    await audioPlayer.play().catch(() => {});
                    updateStatus(ttsStatus, 'Text to speech: playing');
                    return;
                }

                const text = ttsText.value.trim();
                if (!text || !('speechSynthesis' in window)) {
                    return;
                }

                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = ttsLanguage.value;
                const chosenVoice = chooseBrowserVoice();
                if (!chosenVoice?.browserVoice) {
                    updateStatus(ttsStatus, 'Text to speech: no matching voice');
                    statusCopy.textContent = `No browser voice is installed for ${ttsLanguage.value}. Select a cloud/cloned voice or install a matching system voice.`;
                    return;
                }

                utterance.voice = chosenVoice?.provider === 'browser' ? chosenVoice.browserVoice : null;
                utterance.onstart = () => updateStatus(ttsStatus, 'Text to speech: playing');
                utterance.onend = () => updateStatus(ttsStatus, 'Text to speech: finished');
                window.speechSynthesis.speak(utterance);
            };

            const getMicrophonePermission = async () => {
                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error('unsupported-microphone');
                }

                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach((track) => track.stop());
            };

            const microphoneErrorMessage = (errorCode) => ({
                'not-allowed': 'Microphone permission was denied. Click the lock icon near the URL, allow Microphone, then reload this page.',
                'service-not-allowed': 'Microphone service is blocked. Allow microphone access in browser and macOS System Settings.',
                'audio-capture': 'No microphone was found. Connect a microphone and check your macOS input device.',
                'no-speech': 'No speech was detected. Please speak closer to the microphone and try again.',
                'network': 'Speech recognition network service is unavailable. Check your internet connection.',
                'unsupported-microphone': 'This browser cannot request microphone access. Use Chrome or Edge on localhost.',
            }[errorCode] || `Microphone error: ${errorCode || 'unknown'}`);

            const startRecognition = async () => {
                if (!SpeechRecognition) {
                    updateStatus(sttStatus, 'Speech to text: unsupported');
                    statusCopy.textContent = 'This browser does not support microphone speech recognition.';
                    return;
                }

                recordButton && (recordButton.disabled = true);
                recognition?.stop();
                recognition = new SpeechRecognition();
                recognition.lang = sttLanguage.value;
                recognition.continuous = true;
                recognition.interimResults = true;
                recognition.onstart = () => {
                    updateStatus(sttStatus, 'Speech to text: listening');
                    statusCopy.textContent = 'Microphone is active. Please speak now.';
                };
                recognition.onresult = (event) => {
                    let transcript = '';
                    for (let index = 0; index < event.results.length; index += 1) {
                        transcript += event.results[index][0].transcript;
                    }
                    sttText.value = transcript.trim();
                };
                recognition.onerror = (event) => {
                    updateStatus(sttStatus, `Speech to text: ${event.error || 'error'}`);
                    statusCopy.textContent = microphoneErrorMessage(event.error);
                };
                recognition.onend = () => {
                    updateStatus(sttStatus, 'Speech to text: idle');
                    if (recordButton) recordButton.disabled = false;
                };

                try {
                    await getMicrophonePermission();
                    recognition.start();
                } catch (error) {
                    updateStatus(sttStatus, 'Speech to text: microphone unavailable');
                    const errorCode = error.name === 'NotAllowedError'
                        ? 'not-allowed'
                        : (error.name === 'NotFoundError' ? 'audio-capture' : error.message);
                    statusCopy.textContent = microphoneErrorMessage(errorCode);
                    if (recordButton) recordButton.disabled = false;
                }
            };

            document.querySelector('[data-sound-play]')?.addEventListener('click', playVoice);
            document.querySelector('[data-sound-download]')?.addEventListener('click', () => {
                const audioUrl = buildAudioUrl(true);
                if (audioUrl) {
                    window.location.href = audioUrl;
                    statusCopy.textContent = 'Voice download started.';
                }
            });
            document.querySelector('[data-sound-stop]')?.addEventListener('click', () => {
                audioPlayer?.pause();
                if (audioPlayer) audioPlayer.currentTime = 0;
                window.speechSynthesis?.cancel();
                updateStatus(ttsStatus, 'Text to speech: stopped');
            });
            document.querySelector('[data-sound-record]')?.addEventListener('click', startRecognition);
            document.querySelector('[data-sound-stop-record]')?.addEventListener('click', () => {
                recognition?.stop();
                updateStatus(sttStatus, 'Speech to text: stopped');
            });
            document.querySelector('[data-sound-clear-stt]')?.addEventListener('click', () => {
                sttText.value = '';
                updateStatus(sttStatus, 'Speech to text: idle');
            });
            document.querySelector('[data-sound-clear-tts]')?.addEventListener('click', () => {
                ttsText.value = '';
                updateStatus(ttsStatus, 'Text to speech: idle');
            });
            ttsLanguage?.addEventListener('change', loadVoices);
            audioPlayer?.addEventListener('ended', () => updateStatus(ttsStatus, 'Text to speech: finished'));
            window.speechSynthesis?.addEventListener('voiceschanged', loadVoices);
            loadVoices();
        });
    </script>
@endsection
