<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ComingSoonGate;
use App\Support\ComingSoonSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComingSoonController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $enabled = ComingSoonSettings::isEnabled();
        $unlocked = ! $enabled || $this->requestHasValidKey($request);

        return $this->success([
            'enabled'  => $enabled,
            'unlocked' => $unlocked,
            'header'   => ComingSoonSettings::header(),
        ]);
    }

    public function unlock(Request $request): JsonResponse
    {
        if (! ComingSoonSettings::isEnabled()) {
            return $this->success([
                'enabled'  => false,
                'unlocked' => true,
            ], 'Coming soon gate is disabled.');
        }

        $request->validate([
            'key' => ['required', 'string'],
        ]);

        $expected = ComingSoonSettings::accessKey();

        if ($expected === '' || ! hash_equals($expected, (string) $request->input('key'))) {
            return $this->error(null, 'Invalid access key.', 403);
        }

        $cookie = cookie(
            ComingSoonSettings::cookieName(),
            ComingSoonGate::unlockToken(),
            ComingSoonSettings::cookieMinutes(),
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
            'header'   => ComingSoonSettings::header(),
            'key'      => $expected,
        ], 'Access granted.')->withCookie($cookie);
    }

    protected function requestHasValidKey(Request $request): bool
    {
        $expected = ComingSoonSettings::accessKey();

        if ($expected === '') {
            return false;
        }

        $headerName = ComingSoonSettings::header();
        $headerKey = (string) $request->header($headerName, '');

        if ($headerKey !== '' && hash_equals($expected, $headerKey)) {
            return true;
        }

        $cookieName = ComingSoonSettings::cookieName();
        $cookieValue = (string) $request->cookie($cookieName, '');

        return $cookieValue !== '' && hash_equals(ComingSoonGate::unlockToken(), $cookieValue);
    }
}
