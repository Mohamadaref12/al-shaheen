<?php

namespace App\Filament\Resources\Editors\Pages;

use App\Filament\Resources\Editors\EditorResource;
use App\Models\Editor;
use Filament\Resources\Pages\CreateRecord;

class CreateEditor extends CreateRecord
{
    protected static string $resource = EditorResource::class;

    protected function afterCreate(): void
    {
        Editor::create(['user_id' => $this->record->id]);
    }
}
