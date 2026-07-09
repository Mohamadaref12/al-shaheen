<?php

namespace App\Support;

use App\Jobs\SendEmailJob;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SafeMail
{
    public static function queue(string $email, Mailable $mailable): bool
    {
        if (! filled($email)) {
            return false;
        }

        try {
            SendEmailJob::dispatch($email, $mailable);

            return true;
        } catch (Throwable $e) {
            self::logFailure($e, $email, $mailable::class);

            return false;
        }
    }

    public static function logFailure(Throwable $e, ?string $email, string $mailable): void
    {
        Log::warning('Email could not be sent', [
            'email'    => $email,
            'mailable' => $mailable,
            'error'    => $e->getMessage(),
        ]);
    }
}
