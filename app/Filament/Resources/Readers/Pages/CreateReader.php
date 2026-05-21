<?php

namespace App\Filament\Resources\Readers\Pages;

use App\Filament\Resources\Readers\ReaderResource;
use App\Models\Reader;
use Filament\Resources\Pages\CreateRecord;

class CreateReader extends CreateRecord
{
    protected static string $resource = ReaderResource::class;

    protected function afterCreate(): void
    {
        Reader::create(['user_id' => $this->record->id]);
    }
}
