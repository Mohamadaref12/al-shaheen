<?php

namespace App\Observers;

use App\Filament\Resources\Writers\WriterResource;
use App\Models\Writer;
use App\Support\AdminNotifier;
use App\Support\TransactionalMailer;

class WriterObserver
{
    public function created(Writer $writer): void
    {
        if ($writer->application_status !== 'submitted') {
            return;
        }

        $this->notifyStaff($writer);
        $this->notifyWriter($writer, 'writer.application_received');
    }

    public function updated(Writer $writer): void
    {
        if (! $writer->wasChanged('application_status')) {
            return;
        }

        if ($writer->application_status === 'submitted') {
            $this->notifyStaff($writer);
        }

        $key = match ($writer->application_status) {
            'submitted' => 'writer.application_received',
            'approved' => 'writer.application_approved',
            'rejected' => 'writer.application_rejected',
            'suspended' => 'writer.application_suspended',
            default => null,
        };

        if ($key === null) {
            return;
        }

        if ($key === 'writer.application_received' && ! $writer->wasRecentlyCreated) {
            $this->notifyWriter($writer, $key, [
                'notes' => (string) ($writer->reviewer_notes ?? ''),
            ]);

            return;
        }

        if ($key !== 'writer.application_received') {
            $this->notifyWriter($writer, $key, [
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
    private function notifyWriter(Writer $writer, string $key, array $replace = []): void
    {
        $writer->loadMissing('user');

        if (! $writer->user) {
            return;
        }

        TransactionalMailer::sendToUser($writer->user, $key, array_merge([
            'name' => $writer->user->name,
        ], $replace));
    }
}
