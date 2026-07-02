<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class NewsEditorialQueueWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('News in Review')
            ->description('Submitted news awaiting editorial action')
            ->query(fn (): Builder => News::query()
                ->with(['author:id,name', 'category:id,name', 'translations'])
                ->where('status', 'under_review')
                ->orderBy('updated_at'))
            ->paginated([5])
            ->emptyStateHeading('No news in review')
            ->emptyStateDescription('Submitted news items will appear here.')
            ->columns([
                TextColumn::make('display_title')
                    ->label('Title')
                    ->limit(35)
                    ->tooltip(fn (News $record): string => $record->display_title),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->icon(Heroicon::OutlinedUser),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since(),
            ])
            ->recordUrl(fn (News $record): string => NewsResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
