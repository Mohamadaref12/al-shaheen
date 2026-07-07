<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

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
                ->badge(News::query()->where('status', 'published')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published'))
                ->excludeQueryWhenResolvingRecord(),

            'under_review' => Tab::make('In Review')
                ->badge(News::query()->where('status', 'under_review')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'under_review'))
                ->excludeQueryWhenResolvingRecord(),

            'draft' => Tab::make('Drafts')
                ->badge(News::query()->where('status', 'draft')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->excludeQueryWhenResolvingRecord(),

            'archived' => Tab::make('Archived')
                ->badge(News::query()->where('status', 'archived')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->excludeQueryWhenResolvingRecord(),

            'all' => Tab::make('All')
                ->badge(News::query()->count()),
        ];
    }
}
