<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use App\Models\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SocialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type'     => 'nullable|in:saved,following,followers',
                'locale'   => 'nullable|in:ar,en',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            if (! $request->filled('type')) {
                return $this->overview($request);
            }

            return match ($request->input('type')) {
                'saved'     => $this->savedArticles($request),
                'following' => $this->following($request),
                'followers' => $this->myFollowers($request),
            };
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve social data.');
        }
    }

    public static function paginateWriterFollowers(Writer $writer, Request $request): JsonResponse
    {
        $controller = new self;

        try {
            $request->validate([
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $paginator = $writer->followers()
                ->select(['users.id', 'users.name', 'users.country'])
                ->orderByPivot('created_at', 'desc')
                ->paginate((int) $request->input('per_page', 15));

            $items = $paginator->getCollection()->map(function (User $user) {
                $user->setAttribute('followed_at', $user->pivot->created_at);

                return $user;
            });

            return $controller->pagedSuccess(
                $items->values(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Writer followers retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $controller->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $controller->handleException($e, 'Failed to retrieve writer followers.');
        }
    }

    protected function overview(Request $request): JsonResponse
    {
        $user   = $request->user();
        $writer = $user->writer;

        return $this->success([
            'is_writer'             => $writer !== null,
            'saved_articles_count'  => $user->savedArticles()->published()->count(),
            'following_count'       => $user->following()
                ->where('application_status', 'approved')
                ->count(),
            'followers_count'       => $writer
                ? $writer->followers()->count()
                : 0,
        ], 'Social overview retrieved successfully.');
    }

    protected function savedArticles(Request $request): JsonResponse
    {
        $query = $request->user()
            ->savedArticles()
            ->published()
            ->with(['author:id,name', 'primaryCategory:id,name,slug', 'tags:id,name,slug'])
            ->select([
                'articles.id', 'author_id', 'primary_category_id', 'title', 'subtitle', 'slug',
                'excerpt', 'featured_image', 'locale', 'read_time', 'is_breaking',
                'is_premium', 'is_editor_pick', 'views_count', 'published_at',
            ])
            ->orderByPivot('created_at', 'desc');

        if ($request->filled('locale')) {
            $query->where('articles.locale', $request->input('locale'));
        }

        $paginator = $query->paginate((int) $request->input('per_page', 15));

        $items = $paginator->getCollection()->map(function (Article $article) {
            $article->setAttribute('saved_at', $article->pivot->created_at);
            $article->setAttribute('is_saved', true);

            return $article;
        });

        return $this->pagedSuccess(
            $items->values(),
            [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
            'Saved articles retrieved successfully.'
        );
    }

    protected function following(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->following()
            ->where('application_status', 'approved')
            ->with(['user:id,name,country', 'categories:id,name,slug'])
            ->withCount('articles')
            ->orderByPivot('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 15));

        $items = $paginator->getCollection()->map(function (Writer $writer) {
            $writer->setAttribute('followed_at', $writer->pivot->created_at);

            return $writer;
        });

        return $this->pagedSuccess(
            $items->values(),
            [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
            'Following list retrieved successfully.'
        );
    }

    protected function myFollowers(Request $request): JsonResponse
    {
        $writer = $request->user()->writer;

        if (! $writer) {
            return $this->error(null, 'You do not have a writer profile.', 403);
        }

        return self::paginateWriterFollowers($writer, $request);
    }
}
