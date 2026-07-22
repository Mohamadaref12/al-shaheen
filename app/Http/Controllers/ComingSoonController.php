<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ComingSoonGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComingSoonController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (! config('coming_soon.enabled')) {
            return redirect('/');
        }

        return view('coming-soon');
    }

    public function unlock(Request $request): RedirectResponse
    {
        if (! config('coming_soon.enabled')) {
            return redirect('/');
        }

        $request->validate([
            'key' => ['required', 'string'],
        ]);

        $expected = (string) config('coming_soon.access_key');

        if ($expected === '' || ! hash_equals($expected, (string) $request->input('key'))) {
            return back()
                ->withInput()
                ->withErrors(['key' => 'المفتاح غير صحيح.']);
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

        return redirect('/')
            ->withCookie($cookie)
            ->with('status', 'تم فتح الموقع بنجاح.');
    }
}
