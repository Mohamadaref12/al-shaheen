<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SavedArticleController extends Controller
{
    public function toggle(Request $request, int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $user  = $request->user();
            $saved = $user->savedArticles()->where('articles.id', $articleId)->exists();

            if ($saved) {
                $user->savedArticles()->detach($articleId);
                $saved   = false;
                $message = 'Article removed from saved list.';
            } else {
                $user->savedArticles()->attach($articleId, ['created_at' => now()]);
                $saved   = true;
                $message = 'Article saved successfully.';
            }

            return $this->success(
                [
                    'article_id' => $articleId,
                    'saved'      => $saved,
                    'action'     => $saved ? 'saved' : 'unsaved',
                ],
                $message
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to toggle save status.');
        }
    }
}
