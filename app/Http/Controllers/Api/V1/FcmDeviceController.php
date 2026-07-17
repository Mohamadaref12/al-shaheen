<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FcmDevice;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmDeviceController extends Controller
{
    use HttpResponses;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'max:2048'],
            'platform' => ['nullable', 'string', 'in:web,dashboard,android,ios'],
            'locale'   => ['nullable', 'string', 'max:8'],
        ]);

        $platform = $data['platform'] ?? 'web';
        $user = $request->user();

        $device = FcmDevice::query()->updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $user?->id,
                'platform'     => $platform,
                'locale'       => $data['locale'] ?? $user?->locale ?? app()->getLocale(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
                'last_used_at' => now(),
            ]
        );

        return $this->success([
            'id'       => $device->id,
            'platform' => $device->platform,
        ], 'Device registered for push notifications.');
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
        ]);

        FcmDevice::query()
            ->where('token', $data['token'])
            ->when($request->user(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->delete();

        return $this->success(null, 'Device unregistered.');
    }
}
