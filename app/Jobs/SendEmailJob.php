<?php

namespace App\Jobs;

use App\Support\SafeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly string $email,
        public readonly Mailable $mailable,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->email)->send($this->mailable);
        } catch (Throwable $e) {
            SafeMail::logFailure($e, $this->email, $this->mailable::class);
        }
    }
}
