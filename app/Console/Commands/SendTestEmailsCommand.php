<?php

namespace App\Console\Commands;

use App\Mail\PasswordResetCodeMail;
use App\Support\LocaleContext;
use App\Support\TransactionalMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailsCommand extends Command
{
    protected $signature = 'emails:test-all {email : Recipient address} {--locale=en : Locale for emails (en or ar)}';

    protected $description = 'Send all transactional email templates to a test address (synchronously)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $locale = in_array($this->option('locale'), ['ar', 'en'], true)
            ? $this->option('locale')
            : 'en';

        config(['queue.default' => 'sync']);

        $name = 'Mohamad Areef';
        $sampleUrl = config('app.url').'/admin';

        $replace = [
            'title'   => 'Sample Article Title',
            'name'    => 'Test Reader',
            'subject' => 'Sample Contact Subject',
            'author'  => 'Test Author',
            'reply'   => 'Thank you for your message. This is a sample reply from the editorial team.',
            'notes'   => 'Sample editorial notes for testing.',
        ];

        $keys = [
            'staff.article_review',
            'staff.news_review',
            'staff.opinion_review',
            'staff.comment_pending',
            'staff.contact_new',
            'staff.writer_application',
            'staff.submission_review',
            'writer.application_received',
            'writer.application_approved',
            'writer.application_rejected',
            'writer.application_suspended',
            'writer.verified_granted',
            'writer.verified_revoked',
            'writer.article_ready',
            'writer.article_published',
            'writer.article_rejected',
            'writer.news_published',
            'writer.opinion_published',
            'writer.submission_approved',
            'writer.submission_rejected',
            'reader.contact_received',
            'reader.contact_replied',
            'reader.comment_pending',
            'reader.comment_approved',
            'reader.comment_rejected',
            'reader.welcome',
            'reader.newsletter_subscribed',
            'reader.newsletter_unsubscribed',
        ];

        $sent = 0;
        $failed = 0;

        foreach ($keys as $key) {
            try {
                TransactionalMailer::send(
                    email: $email,
                    name: $name,
                    locale: $locale,
                    key: $key,
                    replace: $replace,
                    actionUrl: str_starts_with($key, 'staff.') ? $sampleUrl : null,
                );
                $this->line("  ✓ {$key}");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$key}: {$e->getMessage()}");
                $failed++;
            }
        }

        try {
            LocaleContext::run($locale, function () use ($email, $name, $locale): void {
                Mail::to($email)->send(new PasswordResetCodeMail(
                    name: $name,
                    code: '123456',
                    expiresAt: now()->addMinutes(15),
                    locale: $locale,
                ));
            });
            $this->line('  ✓ reader.password_reset');
            $sent++;
        } catch (\Throwable $e) {
            $this->error("  ✗ reader.password_reset: {$e->getMessage()}");
            $failed++;
        }

        $this->newLine();
        $this->info("Done. Sent: {$sent}, Failed: {$failed} → {$email} ({$locale})");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
