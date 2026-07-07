<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Widgets\Concerns\ConfiguresDashboardTable;
use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EditorialQueueWidget extends TableWidget
{
    use ConfiguresDashboardTable;

    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $this->configureDashboardTable(
            $table
                ->heading('Editorial Queue')
                ->description('Articles waiting for editorial action')
                ->query(fn (): Builder => Article::query()
                    ->with(['author:id,name', 'translations'])
                    ->whereIn('status', ['submitted', 'under_review', 'review', 'ready', 'scheduled'])
                    ->orderByRaw("FIELD(status, 'submitted', 'under_review', 'review', 'ready', 'scheduled')")
                    ->orderBy('submitted_at')
                    ->orderBy('created_at')
                    ->limit(5))
                ->emptyStateHeading('Queue is clear')
                ->emptyStateDescription('No articles are waiting for editorial action.')
                ->columns([
                    $this->dashboardTitleColumn()
                        ->description(fn (Article $record): ?string => $record->author?->name),

                    TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->grow(false)
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'submitted'    => 'Submitted',
                            'under_review' => 'In Review',
                            'review'       => 'Review',
                            'ready'        => 'Ready',
                            'scheduled'    => 'Scheduled',
                            default        => ucfirst(str_replace('_', ' ', $state)),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'ready', 'scheduled' => 'info',
                            default              => 'warning',
                        }),

                    $this->dashboardSinceColumn('submitted_at', 'Submitted')
                        ->placeholder('—'),
                ])
                ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]))
        );
    }
}
