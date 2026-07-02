<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentArticlesWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recently Published')
            ->description('Latest live articles')
            ->query(fn (): Builder => Article::query()
                ->with(['author:id,name', 'primaryCategory:id,name', 'translations'])
                ->where('status', 'published')
                ->orderByDesc('published_at'))
            ->paginated([5])
            ->columns([
                TextColumn::make('display_title')
                    ->label('Title')
                    ->limit(35)
                    ->tooltip(fn (Article $record): string => $record->display_title),

                TextColumn::make('primaryCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->icon(Heroicon::OutlinedEye)
                    ->numeric(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->since(),
            ])
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
