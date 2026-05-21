<?php

namespace App\Filament\Resources\Writers\Pages;

use App\Filament\Resources\Writers\WriterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWriter extends EditRecord
{
    protected static string $resource = WriterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateRecordDataBeforeFill(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $data['name']     = $user->name;
            $data['email']    = $user->email;
            $data['locale']   = $user->locale;
            $data['country']  = $user->country;
            $data['language'] = $user->language;
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $updateData = [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'locale'   => $data['locale'] ?? $user->locale,
                'country'  => $data['country'] ?? null,
                'language' => $data['language'] ?? null,
            ];
            if (isset($data['password'])) {
                $updateData['password'] = $data['password'];
            }
            $user->update($updateData);
        }

        unset($data['name'], $data['email'], $data['password'], $data['locale'], $data['country'], $data['language']);

        return $data;
    }
}
