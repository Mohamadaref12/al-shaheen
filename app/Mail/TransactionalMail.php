<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $mailSubject,
        public readonly string $heading,
        public readonly string $body,
        string $locale = 'en',
        public readonly ?string $actionLabel = null,
        public readonly ?string $actionUrl = null,
    ) {
        $this->locale($locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional', with: [
            'greeting'    => __('emails.greeting', ['name' => $this->recipientName]),
            'heading'     => $this->heading,
            'body'        => $this->body,
            'locale'      => $this->locale,
            'actionLabel' => $this->actionLabel,
            'actionUrl'   => $this->actionUrl,
            'subject'     => $this->mailSubject,
        ]);
    }
}
