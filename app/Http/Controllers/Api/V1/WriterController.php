<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\WriterResource;
use App\Models\Writer;
use App\Traits\MarksSavedArticles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WriterController extends Controller
{
    use MarksSavedArticles;
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

            return $this->success(
                WriterResource::collection($writers)->resolve(),
                'Writers retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writers.');
        }
    }

    public function show(Request $request, int $writerId): JsonResponse
    {
        try {
            $request->validate([
                'locale'   => 'nullable|in:ar,en',
                'per_page' => 'nullable|integer|min:1|max:50',
                'page'     => 'nullable|integer|min:1',
            ]);

            $writer = Writer::with(['user:id,name,country', 'categories:id,name,slug'])
                ->withCount('articles')
                ->where('id', $writerId)
                ->where('application_status', 'approved')
                ->first();

            if (! $writer) {
                return $this->error(null, 'Writer not found.', 404);
            }

            $articlesQuery = $writer->articles()
                ->with(['primaryCategory:id,name,slug', 'tags:id,name,slug'])
                ->select([
                    'articles.id', 'articles.author_id', 'articles.primary_category_id',
                    'articles.title', 'articles.subtitle', 'articles.slug', 'articles.excerpt',
                    'articles.featured_image', 'articles.locale', 'articles.read_time',
                    'articles.is_breaking', 'articles.is_premium', 'articles.views_count',
                    'articles.published_at',
                ])
                ->orderByDesc('articles.published_at');

            if ($request->filled('locale')) {
                $articlesQuery->where('articles.locale', $request->input('locale'));
            }

            $articles = $this->withIsSavedOnPaginator(
                $articlesQuery->paginate((int) $request->input('per_page', 15)),
                $request
            );

            $writer->setRelation('articles', $articles);

            return $this->success(
                WriterResource::make($writer)->resolve(),
                'Writer retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
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
