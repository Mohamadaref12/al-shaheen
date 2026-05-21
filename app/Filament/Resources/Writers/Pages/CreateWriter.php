<?php

namespace App\Filament\Resources\Writers\Pages;

use App\Filament\Resources\Writers\WriterResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateWriter extends CreateRecord
{
    protected static string $resource = WriterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => $data['password'],
            'locale'      => $data['locale'] ?? 'ar',
            'country'     => $data['country'] ?? null,
            'language'    => $data['language'] ?? null,
            'is_active'   => true,
            'is_verified' => false,
        ]);

        $data['user_id'] = $user->id;

        unset($data['name'], $data['email'], $data['password'], $data['locale'], $data['country'], $data['language']);

        return $data;
    }
}
