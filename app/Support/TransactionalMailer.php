<?php

namespace App\Support;

use App\Mail\TransactionalMail;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class TransactionalMailer
{
    /**
     * @param  array<string, string>  $replace
     */
    public static function send(
        string $email,
        string $name,
        string $locale,
        string $key,
        array $replace = [],
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        if (! filled($email)) {
            return;
        }

        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'en';

        App::usingLocale($locale, function () use ($email, $name, $locale, $key, $replace, $actionUrl, $actionLabel): void {
            $replace = self::withNotes($replace);

            Mail::to($email)->queue(new TransactionalMail(
                recipientName: $name,
                mailSubject: __("emails.{$key}.subject", $replace),
                heading: __("emails.{$key}.heading", $replace),
                body: __("emails.{$key}.body", $replace),
                locale: $locale,
                actionLabel: $actionUrl ? ($actionLabel ?? __('emails.actions.view', $replace)) : null,
                actionUrl: $actionUrl,
            ));
        });
    }

    /**
     * @param  array<string, string>  $replace
     */
    public static function sendToUser(
        User $user,
        string $key,
        array $replace = [],
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        self::send(
            email: $user->email,
            name: $user->name,
            locale: $user->locale ?? 'en',
            key: $key,
            replace: $replace,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
        );
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  array<string, string>  $replace
     */
    public static function sendToUsers(
        Collection $users,
        string $key,
        array $replace = [],
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        foreach ($users as $user) {
            self::sendToUser($user, $key, $replace, $actionUrl, $actionLabel);
        }
    }

    /**
     * @param  array<string, string>  $replace
     * @return array<string, string>
     */
    private static function withNotes(array $replace): array
    {
        if (! array_key_exists('notes', $replace)) {
            return $replace;
        }

        $notes = trim((string) $replace['notes']);

        $replace['notes'] = filled($notes)
            ? __('emails.notes_prefix', ['notes' => $notes])
            : '';

        return $replace;
    }
}
