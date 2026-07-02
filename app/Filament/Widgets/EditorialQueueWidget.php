<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EditorialQueueWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Editorial Queue')
            ->description('Articles waiting for editorial action')
            ->query(fn (): Builder => Article::query()
                ->with(['author:id,name', 'primaryCategory:id,name', 'translations'])
                ->whereIn('status', ['submitted', 'under_review', 'review', 'ready', 'scheduled'])
                ->orderByRaw("FIELD(status, 'submitted', 'under_review', 'review', 'ready', 'scheduled')")
                ->orderBy('submitted_at')
                ->orderBy('created_at'))
            ->paginated([5])
            ->columns([
                TextColumn::make('display_title')
                    ->label('Title')
                    ->limit(35)
                    ->tooltip(fn (Article $record): string => $record->display_title),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->icon(Heroicon::OutlinedUser),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'review' => 'Review',
                        'ready' => 'Ready',
                        'scheduled' => 'Scheduled',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ready', 'scheduled' => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->since()
                    ->placeholder('—'),
            ])
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
