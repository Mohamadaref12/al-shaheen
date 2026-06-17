<?php

namespace App\Traits;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

trait MarksSavedArticles
{
    protected function withIsSaved(Article $article, ?Request $request = null): Article
    {
        $request ??= request();
        $article->setAttribute('is_saved', $this->resolveIsSaved($article->id, $request));

        return $article;
    }

    protected function withIsSavedOnCollection(Collection $articles, ?Request $request = null): Collection
    {
        $request ??= request();
        $savedIds = $this->savedArticleIdsFor($articles->pluck('id'), $request);

        return $articles->each(function (Article $article) use ($savedIds): void {
            $article->setAttribute('is_saved', $savedIds->contains($article->id));
        });
    }

    protected function withIsSavedOnPaginator(LengthAwarePaginator $paginator, ?Request $request = null): LengthAwarePaginator
    {
        $collection = $paginator->getCollection();
        $this->withIsSavedOnCollection($collection, $request);
        $paginator->setCollection($collection);

        return $paginator;
    }

    protected function resolveIsSaved(int $articleId, Request $request): bool
    {
        $user = $this->authenticatedUser($request);

        if (! $user) {
            return false;
        }

        return $user->savedArticles()->where('articles.id', $articleId)->exists();
    }

    protected function savedArticleIdsFor(Collection $articleIds, Request $request): Collection
    {
        $user = $this->authenticatedUser($request);

        if (! $user || $articleIds->isEmpty()) {
            return collect();
        }

        return $user->savedArticles()
            ->whereIn('articles.id', $articleIds->unique()->values())
            ->pluck('articles.id');
    }

    protected function authenticatedUser(?Request $request = null)
    {
        $request ??= request();

        if ($user = $request->user()) {
            return $user;
        }

        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        return PersonalAccessToken::findToken($token)?->tokenable;
    }
}
