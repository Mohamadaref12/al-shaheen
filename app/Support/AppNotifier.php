<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\UserAlertNotification;
use App\Services\Firebase\FcmService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class AppNotifier
{
    /**
     * Notify app/website users (database inbox + FCM push).
     *
     * @param  User|Collection<int, User>|iterable<int, User>|null  $recipients
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $platforms
     */
    public static function notify(
        User|iterable|null $recipients,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
        ?array $platforms = ['web', 'android', 'ios', 'dashboard'],
    ): void {
        $users = collect(is_iterable($recipients) ? $recipients : [$recipients])
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send(
            $users,
            new UserAlertNotification($type, $title, $body, $url, $data)
        );

        app(FcmService::class)->sendToUsers(
            $users,
            $title,
            $body,
            $url,
            array_merge(['type' => $type], $data),
            platforms: $platforms,
        );
    }

    public static function notifyOne(
        ?User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
    ): void {
        if (! $user) {
            return;
        }

        self::notify($user, $type, $title, $body, $url, $data);
    }

    /**
     * Public broadcast for website/app subscribers (FCM topic).
     *
     * @param  array<string, mixed>  $data
     */
    public static function broadcast(
        string $topic,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        array $data = [],
    ): void {
        app(FcmService::class)->sendToTopic(
            $topic,
            $title,
            $body,
            $url,
            array_merge(['type' => $type], $data),
        );
    }
}
