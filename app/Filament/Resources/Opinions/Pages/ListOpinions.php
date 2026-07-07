<?php

namespace App\Filament\Resources\Opinions\Pages;

use App\Filament\Resources\Opinions\OpinionResource;
use App\Models\Opinion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOpinions extends ListRecords
{
    protected static string $resource = OpinionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'published';
    }

    public function getTabs(): array
    {
        return [
            'published' => Tab::make('Published')
                ->badge(Opinion::query()->where('status', 'published')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published'))
                ->excludeQueryWhenResolvingRecord(),

            'under_review' => Tab::make('In Review')
                ->badge(Opinion::query()->where('status', 'under_review')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'under_review'))
                ->excludeQueryWhenResolvingRecord(),

            'draft' => Tab::make('Drafts')
                ->badge(Opinion::query()->where('status', 'draft')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->excludeQueryWhenResolvingRecord(),

            'archived' => Tab::make('Archived')
                ->badge(Opinion::query()->where('status', 'archived')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->excludeQueryWhenResolvingRecord(),

            'all' => Tab::make('All')
                ->badge(Opinion::query()->count()),
        ];
    }
}
