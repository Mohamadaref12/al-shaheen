<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ComingSoonGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComingSoonController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $enabled = (bool) config('coming_soon.enabled');
        $unlocked = ! $enabled || $this->requestHasValidKey($request);

        return $this->success([
            'enabled'  => $enabled,
            'unlocked' => $unlocked,
            'header'   => config('coming_soon.header'),
        ]);
    }

    public function unlock(Request $request): JsonResponse
    {
        if (! config('coming_soon.enabled')) {
            return $this->success([
                'enabled'  => false,
                'unlocked' => true,
            ], 'Coming soon gate is disabled.');
        }

        $request->validate([
            'key' => ['required', 'string'],
        ]);

        $expected = (string) config('coming_soon.access_key');

        if ($expected === '' || ! hash_equals($expected, (string) $request->input('key'))) {
            return $this->error(null, 'Invalid access key.', 403);
        }

        $cookie = cookie(
            (string) config('coming_soon.cookie', 'coming_soon_access'),
            ComingSoonGate::unlockToken(),
            (int) config('coming_soon.cookie_minutes', 60 * 24 * 30),
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        );

        return $this->success([
            'enabled'  => true,
            'unlocked' => true,
            'header'   => config('coming_soon.header'),
            'key'      => $expected,
        ], 'Access granted.')->withCookie($cookie);
    }

    protected function requestHasValidKey(Request $request): bool
    {
        $expected = (string) config('coming_soon.access_key');

        if ($expected === '') {
            return false;
        }

        $headerName = (string) config('coming_soon.header', 'X-Coming-Soon-Key');
        $headerKey = (string) $request->header($headerName, '');

        if ($headerKey !== '' && hash_equals($expected, $headerKey)) {
            return true;
        }

        $cookieName = (string) config('coming_soon.cookie', 'coming_soon_access');
        $cookieValue = (string) $request->cookie($cookieName, '');

        return $cookieValue !== '' && hash_equals(ComingSoonGate::unlockToken(), $cookieValue);
    }
}
