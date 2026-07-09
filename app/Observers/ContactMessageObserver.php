<?php

namespace App\Observers;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Support\AdminNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class ContactMessageObserver
{
    public function created(ContactMessage $message): void
    {
        if ($message->status !== 'new') {
            return;
        }

        AdminNotifier::notify(
            AdminNotifier::admins(),
            'New contact message',
            Str::limit($message->subject, 80).' — '.$message->name,
            ContactMessageResource::getUrl('view', ['record' => $message]),
            'info',
            'staff.contact_new',
            [
                'name'    => $message->name,
                'subject' => $message->subject,
            ],
        );

        TransactionalMailer::send(
            email: $message->email,
            name: $message->name,
            locale: 'ar',
            key: 'reader.contact_received',
            replace: [
                'name'    => $message->name,
                'subject' => $message->subject,
            ],
        );
    }
}
