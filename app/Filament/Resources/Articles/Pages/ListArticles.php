<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

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
        $pendingStatuses = ArticleResource::AWAITING_APPROVAL_STATUSES;

        return [
            'published' => Tab::make('Published')
                ->badge(Article::query()->where('status', 'published')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published'))
                ->excludeQueryWhenResolvingRecord(),

            'pending' => Tab::make('In Review')
                ->badge(Article::query()->whereIn('status', $pendingStatuses)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $pendingStatuses))
                ->excludeQueryWhenResolvingRecord(),

            'draft' => Tab::make('Drafts')
                ->badge(Article::query()->where('status', 'draft')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->excludeQueryWhenResolvingRecord(),

            'rejected' => Tab::make('Rejected')
                ->badge(Article::query()->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->excludeQueryWhenResolvingRecord(),

            'archived' => Tab::make('Archived')
                ->badge(Article::query()->where('status', 'archived')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->excludeQueryWhenResolvingRecord(),

            'all' => Tab::make('All')
                ->badge(Article::query()->count()),
        ];
    }
}
