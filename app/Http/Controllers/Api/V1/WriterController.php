<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user   = $request->user();
            $writer = $user->writer;

            if (! $writer) {
                return $this->error(null, 'You do not have a writer profile.', 403);
            }

            $data = $request->validate([
                'display_name'          => 'sometimes|string|max:255',
                'bio'                   => 'nullable|string|max:2000',
                'profile_photo'         => 'nullable|string',
                'portfolio_link'        => 'nullable|url|max:500',
                'experience_level'      => 'nullable|in:junior,mid,senior',
                'languages'             => 'nullable|array',
                'editorial_specialties' => 'nullable|array',
                'location'              => 'nullable|string|max:255',
                'social_links'          => 'nullable|array',
                'media_affiliation'     => 'nullable|string|max:500',
            ]);

            $writer->fill($data)->save();

            return $this->success(
                $writer->fresh(['user:id,name', 'categories:id,name,slug']),
                'Writer profile updated successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update writer profile.');
        }
    }
}
