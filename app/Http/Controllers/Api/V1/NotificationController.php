<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(50, max(1, (int) $request->integer('per_page', 20)));

        $query = $user->notifications()->latest();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $paginator = $query->paginate($perPage);

        return $this->pagedSuccess(
            NotificationResource::collection($paginator->items())->resolve(),
            [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
            'Notifications retrieved successfully.'
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ], 'Unread notifications count.');
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return $this->error(null, 'Notification not found.', 404);
        }

        $notification->markAsRead();

        return $this->success(
            NotificationResource::make($notification->fresh())->resolve(),
            'Notification marked as read.'
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success([
            'unread_count' => 0,
        ], 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $request->user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        if (! $deleted) {
            return $this->error(null, 'Notification not found.', 404);
        }

        return $this->success(null, 'Notification deleted.');
    }
}
