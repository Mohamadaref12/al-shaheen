<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WriterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Writer::with('user:id,name,country')
                ->where('application_status', 'approved')
                ->withCount('articles');

            if ($request->boolean('verified')) {
                $query->where('is_verified_writer', true);
            }
            if ($request->filled('speciality')) {
                $query->whereJsonContains('editorial_specialties', $request->input('speciality'));
            }

            $writers = $query->orderByDesc('created_at')->get();

            return $this->success($writers, 'Writers retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writers.');
        }
    }

    public function show(int $writerId): JsonResponse
    {
        try {
            $writer = Writer::with(['user:id,name,country', 'categories:id,name,slug'])
                ->where('id', $writerId)
                ->where('application_status', 'approved')
                ->first();

            if (! $writer) {
                return $this->error(null, 'Writer not found.', 404);
            }

            return $this->success($writer, 'Writer retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writer.');
        }
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateUserProfileAction $updateProfile): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user->writer) {
                return $this->error(null, 'You do not have a writer profile.', 403);
            }

            $user = $updateProfile->execute($user, $request->validated());

            return $this->success(
                UserResource::makeLoaded($user),
                'Profile updated successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update writer profile.');
        }
    }
}
