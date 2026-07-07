<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Widgets\Concerns\ConfiguresDashboardTable;
use App\Models\Comment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingCommentsWidget extends TableWidget
{
    use ConfiguresDashboardTable;

    protected static bool $isLazy = false;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $this->configureDashboardTable(
            $table
                ->heading('Pending Comments')
                ->description('Comments awaiting moderation')
                ->query(fn (): Builder => Comment::query()
                    ->with(['user:id,name', 'article.translations'])
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(5))
                ->emptyStateHeading('No pending comments')
                ->emptyStateDescription('New comments awaiting moderation will appear here.')
                ->columns([
                    TextColumn::make('user.name')
                        ->label('User')
                        ->weight(FontWeight::SemiBold)
                        ->size(TextSize::Small)
                        ->placeholder('Guest')
                        ->grow(false),

                    TextColumn::make('body')
                        ->label('Comment')
                        ->wrap()
                        ->lineClamp(2)
                        ->size(TextSize::Small)
                        ->description(fn (Comment $record): string => $record->article?->display_title ?? '—')
                        ->extraCellAttributes(['dir' => 'auto']),

                    $this->dashboardSinceColumn('created_at', 'Posted'),
                ])
                ->recordUrl(fn (Comment $record): string => CommentResource::getUrl('edit', ['record' => $record]))
        );
    }
}
