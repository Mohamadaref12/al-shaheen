<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FollowController extends Controller
{
    public function followers(Request $request, int $writerId): JsonResponse
    {
        try {
            $writer = Writer::query()
                ->where('id', $writerId)
                ->where('application_status', 'approved')
                ->first();

            if (! $writer) {
                return $this->error(null, 'Writer not found.', 404);
            }

            return SocialController::paginateWriterFollowers($writer, $request);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writer followers.');
        }
    }

    public function toggle(Request $request, int $writerId): JsonResponse
    {
        try {
            $writer = Writer::query()
                ->where('id', $writerId)
                ->where('application_status', 'approved')
                ->first();

            if (! $writer) {
                return $this->error(null, 'Writer not found.', 404);
            }

            if ($writer->user_id === $request->user()->id) {
                return $this->error(null, 'You cannot follow yourself.', 422);
            }

            $user      = $request->user();
            $following = $user->following()->where('writer.id', $writerId)->exists();

            if ($following) {
                $user->following()->detach($writerId);
                $following = false;
                $message   = 'Writer unfollowed successfully.';
            } else {
                $user->following()->attach($writerId, ['created_at' => now()]);
                $following = true;
                $message   = 'Writer followed successfully.';
            }

            return $this->success(
                [
                    'writer_id' => $writerId,
                    'following' => $following,
                    'action'    => $following ? 'followed' : 'unfollowed',
                ],
                $message
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to toggle follow status.');
        }
    }
}
