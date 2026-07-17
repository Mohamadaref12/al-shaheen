<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmDeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'  => ['required', 'string', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:8'],
        ]);

        $user = $request->user();

        $device = FcmDevice::query()->updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $user->id,
                'platform'     => 'dashboard',
                'locale'       => $data['locale'] ?? $user->locale ?? app()->getLocale(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'ok'       => true,
            'id'       => $device->id,
            'platform' => $device->platform,
        ]);
    }
}
