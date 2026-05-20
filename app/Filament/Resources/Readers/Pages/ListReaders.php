<?php

namespace App\Filament\Resources\Readers\Pages;

use App\Filament\Resources\Readers\ReaderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReaders extends ListRecords
{
    protected static string $resource = ReaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
