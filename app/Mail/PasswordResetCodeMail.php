<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PasswordResetCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly \Illuminate\Support\Carbon $expiresAt,
        public readonly string $locale = 'ar',
    ) {}

    public function envelope(): Envelope
    {
        return App::usingLocale($this->locale, fn () => new Envelope(
            subject: __('emails.reader.password_reset.subject'),
        ));
    }

    public function content(): Content
    {
        return App::usingLocale($this->locale, fn () => new Content(
            view: 'emails.password-reset-code',
            with: [
                'greeting' => __('emails.greeting', ['name' => $this->name]),
                'heading'  => __('emails.reader.password_reset.heading'),
                'body'     => __('emails.reader.password_reset.body', [
                    'code'    => $this->code,
                    'expires' => $this->expiresAt->timezone(config('app.timezone'))->format('H:i'),
                ]),
                'locale'   => $this->locale,
                'subject'  => __('emails.reader.password_reset.subject'),
            ],
        ));
    }
}
