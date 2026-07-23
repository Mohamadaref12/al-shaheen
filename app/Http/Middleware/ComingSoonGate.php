<?php

namespace App\Http\Middleware;

use App\Support\ComingSoonSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ComingSoonGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ComingSoonSettings::isEnabled()) {
            return $next($request);
        }

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if ($this->isUnlocked($request)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'status'  => 'error',
            'message' => 'Coming soon. Provide a valid access key.',
            'data'    => [
                'coming_soon' => true,
                'unlock_url'  => url('/api/v1/coming-soon/unlock'),
            ],
        ], 503);
    }

    protected function shouldBypass(Request $request): bool
    {
        $path = ltrim($request->path(), '/');

        foreach (config('coming_soon.except', []) as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    protected function isUnlocked(Request $request): bool
    {
        $expectedKey = ComingSoonSettings::accessKey();

        if ($expectedKey === '') {
            return false;
        }

        $headerName = ComingSoonSettings::header();
        $headerKey = (string) $request->header($headerName, '');

        if ($headerKey !== '' && hash_equals($expectedKey, $headerKey)) {
            return true;
        }

        $cookieName = ComingSoonSettings::cookieName();
        $cookieValue = (string) $request->cookie($cookieName, '');

        return $cookieValue !== '' && hash_equals(self::unlockToken(), $cookieValue);
    }

    public static function unlockToken(): string
    {
        return hash_hmac(
            'sha256',
            ComingSoonSettings::accessKey(),
            (string) config('app.key')
        );
    }
}
