<?php

namespace App\Filament\Resources\Writers\Pages;

use App\Filament\Resources\Writers\WriterResource;
use App\Models\Writer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWriters extends ListRecords
{
    protected static string $resource = WriterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'regular' => Tab::make('Regular Writers')
                ->badge(Writer::query()->where('is_verified_writer', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified_writer', false))
                ->excludeQueryWhenResolvingRecord(),

            'verified_tier' => Tab::make('Verified Tier')
                ->badge(Writer::query()->where('is_verified_writer', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified_writer', true))
                ->excludeQueryWhenResolvingRecord(),
        ];
    }
}
