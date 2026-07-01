<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(ContactMessage::query()->count()),

            'unread' => Tab::make('Unread')
                ->badge(ContactMessage::query()->unread()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->unread())
                ->excludeQueryWhenResolvingRecord(),

            'read' => Tab::make('Read')
                ->badge(ContactMessage::query()->where('status', 'read')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'read'))
                ->excludeQueryWhenResolvingRecord(),

            'replied' => Tab::make('Replied')
                ->badge(ContactMessage::query()->where('status', 'replied')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'replied'))
                ->excludeQueryWhenResolvingRecord(),
        ];
    }
}
