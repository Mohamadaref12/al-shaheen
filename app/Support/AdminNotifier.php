<?php

namespace App\Support;

use App\Models\User;
use App\Services\Firebase\FcmService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class AdminNotifier
{
    /**
     * @return Collection<int, User>
     */
    public static function editorsAndAdmins(?int $exceptUserId = null): Collection
    {
        return User::query()
            ->where(fn ($query) => $query
                ->whereHas('editor')
                ->orWhereHas('admin'))
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function admins(?int $exceptUserId = null): Collection
    {
        return User::query()
            ->whereHas('admin')
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->get();
    }

    /**
     * @param  array<string, string>  $mailReplace
     */
    public static function notify(
        Collection $recipients,
        string $title,
        ?string $body = null,
        ?string $url = null,
        string $status = 'info',
        ?string $mailKey = null,
        array $mailReplace = [],
    ): void {
        if ($recipients->isEmpty()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->status($status);

        if ($body) {
            $notification->body($body);
        }

        if ($url) {
            $notification->actions([
                Action::make('view')
                    ->label('View')
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($recipients, isEventDispatched: true);

        app(FcmService::class)->sendToUsers(
            $recipients,
            $title,
            $body,
            $url,
            [
                'status' => $status,
                'source' => 'dashboard',
                'type'   => 'staff_alert',
            ],
            platforms: ['dashboard', 'web', 'android', 'ios'],
        );

        if ($mailKey) {
            TransactionalMailer::sendToUsers($recipients, $mailKey, $mailReplace, $url);
        }
    }
}
