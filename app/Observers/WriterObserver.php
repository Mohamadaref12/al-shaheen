<?php

namespace App\Observers;

use App\Filament\Resources\Writers\WriterResource;
use App\Models\Writer;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
use App\Support\TransactionalMailer;

class WriterObserver
{
    public function created(Writer $writer): void
    {
        if ($writer->application_status !== 'submitted') {
            return;
        }

        $this->notifyStaff($writer);
        $this->notifyWriter($writer, 'writer.application_received', 'application_received', 'Application received');
    }

    public function updated(Writer $writer): void
    {
        if (! $writer->wasChanged('application_status')) {
            return;
        }

        if ($writer->application_status === 'submitted') {
            $this->notifyStaff($writer);
        }

        $map = match ($writer->application_status) {
            'submitted' => ['writer.application_received', 'application_received', 'Application received'],
            'approved' => ['writer.application_approved', 'application_approved', 'Application approved'],
            'rejected' => ['writer.application_rejected', 'application_rejected', 'Application rejected'],
            'suspended' => ['writer.application_suspended', 'application_suspended', 'Account suspended'],
            default => null,
        };

        if ($map === null) {
            return;
        }

        [$mailKey, $type, $title] = $map;

        if ($mailKey === 'writer.application_received' && ! $writer->wasRecentlyCreated) {
            $this->notifyWriter($writer, $mailKey, $type, $title, [
                'notes' => (string) ($writer->reviewer_notes ?? ''),
            ]);

            return;
        }

        if ($mailKey !== 'writer.application_received') {
            $this->notifyWriter($writer, $mailKey, $type, $title, [
                'notes' => (string) ($writer->reviewer_notes ?? ''),
            ]);
        }
    }

    private function notifyStaff(Writer $writer): void
    {
        $writer->loadMissing('user');

        $name = $writer->display_name ?: $writer->user?->name ?: 'Writer #'.$writer->id;

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(auth()->id()),
            'New writer application',
            $name.' applied to join as a writer.',
            WriterResource::getUrl('edit', ['record' => $writer]),
            'warning',
            'staff.writer_application',
            ['name' => $name],
        );
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function notifyWriter(
        Writer $writer,
        string $mailKey,
        string $type,
        string $title,
        array $replace = [],
    ): void {
        $writer->loadMissing('user');

        if (! $writer->user) {
            return;
        }

        $payload = array_merge([
            'name' => $writer->user->name,
        ], $replace);

        TransactionalMailer::sendToUser($writer->user, $mailKey, $payload);

        AppNotifier::notifyOne(
            $writer->user,
            $type,
            $title,
            $payload['notes'] ?? null,
            null,
            ['writer_id' => (string) $writer->id],
        );
    }
}
