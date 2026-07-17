<?php

namespace App\Services\Firebase;

use App\Models\FcmDevice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmService
{
    public function enabled(): bool
    {
        if (! config('firebase.enabled')) {
            return false;
        }

        $credentials = $this->credentials();

        return is_array($credentials)
            && filled($credentials['client_email'] ?? null)
            && filled($credentials['private_key'] ?? null)
            && ! str_contains((string) ($credentials['private_key'] ?? ''), 'REPLACE_ME')
            && ! str_contains((string) ($credentials['client_email'] ?? ''), 'REPLACE_ME')
            && filled(config('firebase.project_id'));
    }

    /**
     * @param  User|Collection<int, User>|iterable<int, User>  $users
     * @param  array<string, mixed>  $data
     */
    public function sendToUsers(
        User|iterable $users,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
        ?array $platforms = null,
    ): void {
        if (! $this->enabled()) {
            return;
        }

        $userIds = collect(is_iterable($users) ? $users : [$users])
            ->map(fn ($user) => $user instanceof User ? $user->id : (int) $user)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $query = FcmDevice::query()->whereIn('user_id', $userIds);

        if ($platforms) {
            $query->whereIn('platform', $platforms);
        }

        $tokens = $query->pluck('token')->filter()->unique()->values();

        $this->sendToTokens($tokens, $title, $body, $url, $data);
    }

    /**
     * Website / topic broadcast (no user required).
     *
     * @param  array<string, mixed>  $data
     */
    public function sendToTopic(
        string $topic,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
    ): void {
        if (! $this->enabled()) {
            return;
        }

        $this->dispatchMessage([
            'topic' => $topic,
            'notification' => array_filter([
                'title' => $title,
                'body'  => $body,
            ]),
            'android' => $this->androidPayload($title, $body, $url, $data),
            'apns' => $this->apnsPayload($title, $body, $url, $data),
            'webpush' => $this->webPushPayload($title, $body, $url, $data),
            'data' => $this->stringData($data + array_filter([
                'url'   => $url,
                'title' => $title,
                'body'  => $body,
            ])),
        ]);
    }

    /**
     * @param  Collection<int, string>|iterable<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function sendToTokens(
        iterable $tokens,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
    ): void {
        if (! $this->enabled()) {
            return;
        }

        collect($tokens)
            ->filter()
            ->unique()
            ->values()
            ->chunk(400)
            ->each(function (Collection $chunk) use ($title, $body, $url, $data): void {
                foreach ($chunk as $token) {
                    $this->dispatchMessage([
                        'token' => $token,
                        'notification' => array_filter([
                            'title' => $title,
                            'body'  => $body,
                        ]),
                        'android' => $this->androidPayload($title, $body, $url, $data),
                        'apns' => $this->apnsPayload($title, $body, $url, $data),
                        'webpush' => $this->webPushPayload($title, $body, $url, $data),
                        'data' => $this->stringData($data + array_filter([
                            'url'   => $url,
                            'title' => $title,
                            'body'  => $body,
                        ])),
                    ], $token);
                }
            });
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function dispatchMessage(array $message, ?string $tokenForCleanup = null): void
    {
        try {
            $accessToken = $this->accessToken();
            $projectId = config('firebase.project_id');

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->withOptions($this->httpOptions())
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    ['message' => $message]
                );

            if ($response->successful()) {
                Log::info('FCM send succeeded', [
                    'name' => data_get($response->json(), 'name'),
                ]);

                return;
            }

            $errorCode = data_get($response->json(), 'error.details.0.errorCode')
                ?? data_get($response->json(), 'error.status');

            if (
                $tokenForCleanup
                && in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)
            ) {
                FcmDevice::where('token', $tokenForCleanup)->delete();
            }

            Log::warning('FCM send failed', [
                'status' => $response->status(),
                'body'   => $response->json() ?? $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('FCM send exception', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function androidPayload(string $title, ?string $body, ?string $url, array $data): array
    {
        return [
            'priority' => 'high',
            'notification' => array_filter([
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'channel_id' => 'al_shaheen_default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]),
            'data' => $this->stringData($data + array_filter([
                'url'   => $url,
                'title' => $title,
                'body'  => $body,
            ])),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function apnsPayload(string $title, ?string $body, ?string $url, array $data): array
    {
        return [
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => array_filter([
                        'title' => $title,
                        'body'  => $body,
                    ]),
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'url' => $url,
                'data' => $data,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function webPushPayload(string $title, ?string $body, ?string $url, array $data): array
    {
        $icon = url('/al-shaheen.png');

        return [
            'headers' => [
                'Urgency' => 'high',
                'TTL'     => '86400',
            ],
            'notification' => array_filter([
                'title'              => $title,
                'body'               => $body,
                'icon'               => $icon,
                'requireInteraction' => true,
            ]),
            'fcm_options' => array_filter([
                'link' => $url,
            ]),
            'data' => $this->stringData($data + array_filter([
                'url' => $url,
            ])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function httpOptions(): array
    {
        $configured = (string) ini_get('curl.cainfo');
        $herdCa = getenv('USERPROFILE')
            ? rtrim((string) getenv('USERPROFILE'), '\\/') . '/.config/herd/config/php/cacert.pem'
            : null;

        if ($configured !== '' && is_file($configured)) {
            return ['verify' => $configured];
        }

        if (is_string($herdCa) && is_file($herdCa)) {
            return ['verify' => $herdCa];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringData(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $out[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $out;
    }

    private function accessToken(): string
    {
        return Cache::remember('firebase.fcm_access_token', 3000, function (): string {
            $credentials = $this->credentials();

            if (! $credentials) {
                throw new \RuntimeException('Firebase credentials are missing.');
            }

            $now = time();
            $jwt = $this->encodeJwt([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()
                ->withOptions($this->httpOptions())
                ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (! $response->successful() || blank($response->json('access_token'))) {
                throw new \RuntimeException('Unable to obtain Firebase access token: ' . $response->body());
            }

            return (string) $response->json('access_token');
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function credentials(): ?array
    {
        $path = config('firebase.credentials');

        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodeJwt(array $payload, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';

        $ok = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $ok) {
            throw new \RuntimeException('Unable to sign Firebase JWT.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
