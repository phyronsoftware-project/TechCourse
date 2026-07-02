<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'password_confirmation' => $request->input('password_confirmation', $request->input('confirm_password')),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => Str::lower($request->string('email')->toString()),
            'password' => $request->string('password')->toString(),
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $payload['phone'] = $request->filled('phone') ? $request->string('phone')->toString() : null;
        }

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = 'user';
        }

        if (Schema::hasColumn('users', 'status')) {
            $payload['status'] = 'active';
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $payload['email_verified_at'] = now();
        }

        $user = User::query()->create($payload);
        event(new Registered($user));

        $this->createAuthNotification($user, 'register');

        $token = $user->createToken(
            $request->input('device_name', 'flutter-app'),
            ['*']
        )->plainTextToken;

        return $this->successResponse('Register successful.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = User::query()
            ->where('email', Str::lower($request->string('email')->toString()))
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return $this->errorResponse('Invalid email or password.', 401, [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (Schema::hasColumn('users', 'status') && ($user->status ?? 'active') !== 'active') {
            return $this->errorResponse('This account is not active.', 403, [
                'status' => ['Your account is inactive or blocked.'],
            ]);
        }

        $this->createAuthNotification($user, 'login');

        $token = $user->createToken(
            $request->input('device_name', 'flutter-app'),
            ['*']
        )->plainTextToken;

        return $this->successResponse('Login successful.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse('Authenticated user fetched successfully.', [
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse('Logout successful.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $status = Password::sendResetLink([
            'email' => Str::lower($request->string('email')->toString()),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->errorResponse(__($status), 422, [
                'email' => [__($status)],
            ]);
        }

        return $this->successResponse(__($status));
    }

    public function socialProviders(): JsonResponse
    {
        return $this->successResponse('Social auth providers fetched successfully.', [
            'providers' => [
                [
                    'name' => 'google',
                    'enabled' => filled(config('services.google.client_id')),
                    'start_url' => route('api.app.auth.social.start', ['provider' => 'google']),
                    'callback_url' => route('api.app.auth.social.callback', ['provider' => 'google']),
                ],
                [
                    'name' => 'facebook',
                    'enabled' => filled(config('services.facebook.client_id')),
                    'start_url' => route('api.app.auth.social.start', ['provider' => 'facebook']),
                    'callback_url' => route('api.app.auth.social.callback', ['provider' => 'facebook']),
                ],
                [
                    'name' => 'telegram',
                    'enabled' => filled(config('services.telegram.bot_name')) && filled(config('services.telegram.bot_token')),
                    'bot_name' => config('services.telegram.bot_name'),
                    'login_url' => route('api.app.auth.social.telegram.login'),
                ],
            ],
        ]);
    }

    public function socialStart(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        $appRedirect = $this->validatedAppRedirect($request->query('app_redirect'));
        $flowId = Str::uuid()->toString();

        Cache::put($this->socialFlowCacheKey($flowId), [
            'provider' => $provider,
            'app_redirect' => $appRedirect,
        ], now()->addMinutes(10));

        $request->session()->put('app_social_auth_flow_id', $flowId);

        $driver = Socialite::driver($provider);

        if (in_array($provider, ['google', 'facebook'], true)) {
            $driver->scopes(['email']);
        }

        if ($provider === 'google') {
            $driver->with([
                'prompt' => 'select_account',
            ]);
        }

        return $driver->redirect();
    }

    public function socialCallback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        $flowId = (string) $request->session()->pull('app_social_auth_flow_id', '');
        $appRedirect = $this->defaultAppRedirect();

        if ($flowId !== '') {
            $flow = Cache::pull($this->socialFlowCacheKey($flowId));
            $appRedirect = $this->validatedAppRedirect(data_get($flow, 'app_redirect')) ?: $appRedirect;
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()->away($this->buildAppRedirectWithFragment($appRedirect, [
                'success' => 'false',
                'provider' => $provider,
                'error' => 'social_login_failed',
            ]));
        }

        $email = (string) ($socialUser->getEmail() ?? '');

        if (! filled($email)) {
            return redirect()->away($this->buildAppRedirectWithFragment($appRedirect, [
                'success' => 'false',
                'provider' => $provider,
                'error' => 'social_email_missing',
            ]));
        }

        $result = $this->resolveSocialUser(
            provider: $provider,
            email: $email,
            name: $socialUser->getName() ?: $socialUser->getNickname() ?: Str::before($email, '@'),
            avatar: $socialUser->getAvatar(),
        );

        if (! $result['success']) {
            return redirect()->away($this->buildAppRedirectWithFragment($appRedirect, [
                'success' => 'false',
                'provider' => $provider,
                'error' => $result['error'],
            ]));
        }

        /** @var User $user */
        $user = $result['user'];
        $token = $user->createToken('flutter-social-' . $provider, ['*'])->plainTextToken;

        return redirect()->away($this->buildAppRedirectWithFragment($appRedirect, [
            'success' => 'true',
            'provider' => $provider,
            'token_type' => 'Bearer',
            'access_token' => $token,
            'is_new_user' => $result['registered'] ? 'true' : 'false',
        ]));
    }

    public function telegramLogin(Request $request): JsonResponse
    {
        $botToken = trim((string) config('services.telegram.bot_token'));

        if ($botToken === '' || ! Schema::hasColumn('users', 'telegram_id')) {
            return $this->errorResponse('Telegram login is not configured yet.', 422, [
                'telegram' => ['Telegram bot settings or database columns are missing.'],
            ]);
        }

        $telegramUser = $this->validatedTelegramUser($request, $botToken);

        if ($telegramUser === null) {
            return $this->errorResponse('Telegram login failed.', 422, [
                'telegram' => ['Telegram payload is invalid or expired.'],
            ]);
        }

        $existing = User::query()
            ->where('telegram_id', $telegramUser['id'])
            ->orWhere('email', $this->telegramPlaceholderEmail($telegramUser['id']))
            ->first();

        if ($existing && in_array($existing->role, ['admin', 'super_admin'], true)) {
            return $this->errorResponse('This Telegram account belongs to an admin account.', 403, [
                'telegram' => ['Please continue from the admin login form.'],
            ]);
        }

        if ($existing && ($existing->status ?? 'active') !== 'active') {
            return $this->errorResponse('This account is not active.', 403, [
                'status' => ['Your account is inactive or blocked.'],
            ]);
        }

        $registeredNow = false;
        $payload = [
            'name' => $telegramUser['name'],
            'email' => $this->telegramPlaceholderEmail($telegramUser['id']),
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'telegram_id')) {
            $payload['telegram_id'] = $telegramUser['id'];
        }

        if (Schema::hasColumn('users', 'telegram_username')) {
            $payload['telegram_username'] = $telegramUser['username'];
        }

        if (Schema::hasColumn('users', 'telegram_photo_url')) {
            $payload['telegram_photo_url'] = $telegramUser['photo_url'];
        }

        if (Schema::hasColumn('users', 'avatar')) {
            $payload['avatar'] = $telegramUser['photo_url'];
        }

        if (! $existing) {
            $registeredNow = true;

            if (Schema::hasColumn('users', 'role')) {
                $payload['role'] = 'user';
            }

            if (Schema::hasColumn('users', 'status')) {
                $payload['status'] = 'active';
            }

            $existing = User::query()->create($payload);
        } else {
            unset($payload['email'], $payload['password']);

            $existing->forceFill(array_filter($payload, static fn ($value) => filled($value) || $value === null))->save();
        }

        $this->createAuthNotification($existing, $registeredNow ? 'register' : 'login');
        $token = $existing->createToken('flutter-social-telegram', ['*'])->plainTextToken;

        return $this->successResponse('Telegram login successful.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'is_new_user' => $registeredNow,
            'user' => $this->userPayload($existing),
        ]);
    }

    protected function createAuthNotification(User $user, string $event): void
    {
        $messages = [
            'login' => [
                'title' => 'Login Successful',
                'message' => 'Welcome back, ' . $user->name . '. Your account login was completed successfully.',
            ],
            'register' => [
                'title' => 'Welcome to TechCourse',
                'message' => 'Hi ' . $user->name . ', your account is ready. Start exploring your courses now.',
            ],
        ];

        if (! isset($messages[$event])) {
            return;
        }

        $this->notificationService->createSpecificNotification([
            'user_id' => $user->id,
            'title' => $messages[$event]['title'],
            'message' => $messages[$event]['message'],
            'channel' => 'all',
            'audience' => 'users',
            'style' => 'success',
            'trigger_event' => $event,
            'send_as_popup' => true,
            'is_active' => true,
        ]);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'province' => $user->province,
            'postal_code' => $user->postal_code,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role ?? 'user',
            'status' => $user->status ?? 'active',
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
        ];
    }

    protected function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => (object) $data,
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status);
    }

    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('The given data was invalid.', 422, $errors);
    }

    protected function resolveSocialUser(string $provider, string $email, string $name, ?string $avatar): array
    {
        $user = User::query()->firstWhere('email', $email);

        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
            return [
                'success' => false,
                'error' => 'admin_account_not_allowed',
            ];
        }

        if ($user && ($user->status ?? 'active') !== 'active') {
            return [
                'success' => false,
                'error' => 'account_inactive',
            ];
        }

        if (! $user) {
            $registeredNow = true;
            $payload = [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ];

            if (Schema::hasColumn('users', 'avatar')) {
                $payload['avatar'] = $avatar;
            }

            if (Schema::hasColumn('users', 'role')) {
                $payload['role'] = 'user';
            }

            if (Schema::hasColumn('users', 'status')) {
                $payload['status'] = 'active';
            }

            $user = User::query()->create($payload);
        } else {
            $registeredNow = false;
            $updates = [];

            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (Schema::hasColumn('users', 'avatar') && filled($avatar)) {
                $updates['avatar'] = $avatar;
            }

            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }
        }

        $this->createAuthNotification($user, $registeredNow ? 'register' : 'login');

        return [
            'success' => true,
            'registered' => $registeredNow,
            'user' => $user,
        ];
    }

    protected function validatedTelegramUser(Request $request, string $botToken): ?array
    {
        $authData = $request->only([
            'id',
            'first_name',
            'last_name',
            'username',
            'photo_url',
            'auth_date',
            'hash',
        ]);

        if (
            ! filled($authData['id'] ?? null)
            || ! filled($authData['auth_date'] ?? null)
            || ! filled($authData['hash'] ?? null)
        ) {
            return null;
        }

        $receivedHash = (string) $authData['hash'];
        unset($authData['hash']);
        ksort($authData);

        $checkString = collect($authData)
            ->filter(static fn ($value) => filled($value))
            ->map(static fn ($value, $key) => $key . '=' . $value)
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $checkString, $secretKey);

        if (! hash_equals($calculatedHash, $receivedHash)) {
            return null;
        }

        if ((int) $authData['auth_date'] < now()->subMinutes(10)->timestamp) {
            return null;
        }

        $fullName = trim(implode(' ', array_filter([
            $authData['first_name'] ?? null,
            $authData['last_name'] ?? null,
        ])));

        return [
            'id' => (int) $authData['id'],
            'name' => $fullName !== '' ? $fullName : ((string) ($authData['username'] ?? 'Telegram User')),
            'username' => filled($authData['username'] ?? null) ? (string) $authData['username'] : null,
            'photo_url' => filled($authData['photo_url'] ?? null) ? (string) $authData['photo_url'] : null,
        ];
    }

    protected function telegramPlaceholderEmail(int $telegramId): string
    {
        return 'telegram_' . $telegramId . '@telegram.local';
    }

    protected function validatedAppRedirect(?string $redirect): ?string
    {
        if (! filled($redirect)) {
            return null;
        }

        $redirect = trim((string) $redirect);

        if (preg_match('/^https?:\/\//i', $redirect)) {
            return null;
        }

        return preg_match('/^[a-z][a-z0-9+\-.]*:\/\/.+/i', $redirect) ? $redirect : null;
    }

    protected function defaultAppRedirect(): string
    {
        return env('APP_MOBILE_REDIRECT_URI', 'techcourse://auth/callback');
    }

    protected function socialFlowCacheKey(string $flowId): string
    {
        return 'app_social_auth_flow:' . $flowId;
    }

    protected function buildAppRedirectWithFragment(string $base, array $payload): string
    {
        return rtrim($base, '#') . '#' . http_build_query($payload);
    }
}
