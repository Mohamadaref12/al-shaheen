<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CommentController extends Controller
{
    public function index(int $articleId): JsonResponse
    {
        try {
            $articleModel = Article::find($articleId);

            if (! $articleModel) {
                return $this->error(null, 'Article not found.', 404);
            }

            $comments = Comment::with(['user:id,name', 'replies' => fn ($q) => $q
                ->where('status', 'approved')
                ->with('user:id,name')])
                ->where('article_id', $articleId)
                ->whereNull('parent_id')
                ->where('status', 'approved')
                ->orderByDesc('created_at')
                ->get();

            return $this->success($comments, 'Comments retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve comments.');
        }
    }

    public function store(Request $request, int $articleId): JsonResponse
    {
        try {
            $articleModel = Article::where('id', $articleId)
                ->where('status', 'published')
                ->first();

            if (! $articleModel) {
                return $this->error(null, 'Article not found.', 404);
            }

            $data = $request->validate([
                'body'      => 'required|string|max:2000',
                'parent_id' => 'nullable|exists:comments,id',
            ]);

            $comment = Comment::create([
                'user_id'    => $request->user()->id,
                'article_id' => $articleId,
                'parent_id'  => $data['parent_id'] ?? null,
                'body'       => $data['body'],
                'status'     => 'pending',
            ]);

            return $this->success(
                $comment->load('user:id,name'),
                'Comment submitted and pending review.',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to submit comment.');
        }
    }

    public function destroy(Request $request, int $commentId): JsonResponse
    {
        try {
            $comment = Comment::find($commentId);

            if (! $comment) {
                return $this->error(null, 'Comment not found.', 404);
            }

            if ($comment->user_id !== $request->user()->id && ! $request->user()->admin()->exists()) {
                return $this->error(null, 'You are not authorized to delete this comment.', 403);
            }

            $comment->delete();

            return $this->success(null, 'Comment deleted successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete comment.');
        }
    }
}
