<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending')
                ->badge(Comment::query()->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->excludeQueryWhenResolvingRecord(),

            'approved' => Tab::make('Approved')
                ->badge(Comment::query()->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->excludeQueryWhenResolvingRecord(),

            'rejected' => Tab::make('Rejected')
                ->badge(Comment::query()->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->excludeQueryWhenResolvingRecord(),

            'all' => Tab::make('All')
                ->badge(Comment::query()->count()),
        ];
    }
}
