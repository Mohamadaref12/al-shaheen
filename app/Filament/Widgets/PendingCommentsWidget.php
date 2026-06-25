<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingCommentsWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending Comments')
            ->description('Comments awaiting moderation')
            ->query(fn (): Builder => Comment::query()
                ->with(['user:id,name', 'article:id,title'])
                ->where('status', 'pending')
                ->latest())
            ->paginated([5])
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Guest'),

                TextColumn::make('article.title')
                    ->label('Article')
                    ->limit(40)
                    ->tooltip(fn (Comment $record): ?string => $record->article?->title),

                TextColumn::make('body')
                    ->limit(60)
                    ->tooltip(fn (Comment $record): string => $record->body),

                TextColumn::make('created_at')
                    ->label('Posted')
                    ->since(),
            ])
            ->recordUrl(fn (Comment $record): string => CommentResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
