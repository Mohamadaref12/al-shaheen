<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Firebase\FcmService;
use Illuminate\Console\Command;

class SendTestFcmNotification extends Command
{
    protected $signature = 'firebase:test
        {--email= : Send to a specific user email}
        {--topic= : Send to an FCM topic (e.g. articles)}
        {--title=Al Shaheen 360 Test}
        {--body=Push notifications are working.}';

    protected $description = 'Send a test Firebase Cloud Messaging notification';

    public function handle(FcmService $fcm): int
    {
        if (! $fcm->enabled()) {
            $this->error('Firebase is disabled or credentials are missing.');
            $this->line('Set FIREBASE_ENABLED=true and place the service account JSON at FIREBASE_CREDENTIALS.');

            return self::FAILURE;
        }

        $title = (string) $this->option('title');
        $body = (string) $this->option('body');

        if ($topic = $this->option('topic')) {
            $fcm->sendToTopic($topic, $title, $body, url('/'));
            $this->info("Sent to topic: {$topic}");

            return self::SUCCESS;
        }

        $email = $this->option('email');

        if (! $email) {
            $this->error('Provide --email=user@example.com or --topic=articles');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User not found: {$email}");

            return self::FAILURE;
        }

        if ($user->fcmDevices()->count() === 0) {
            $this->warn('User has no registered FCM devices yet. Open the dashboard/website and allow notifications first.');
        }

        $fcm->sendToUsers($user, $title, $body, url('/admin'));
        $this->info("Sent to user: {$email}");

        return self::SUCCESS;
    }
}
