<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\News\NewsResource;
use App\Filament\Widgets\Concerns\ConfiguresDashboardTable;
use App\Models\News;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class NewsEditorialQueueWidget extends TableWidget
{
    use ConfiguresDashboardTable;

    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $this->configureDashboardTable(
            $table
                ->heading('News in Review')
                ->description('Submitted news awaiting editorial action')
                ->query(fn (): Builder => News::query()
                    ->with(['author:id,name', 'category:id,name', 'translations'])
                    ->where('status', 'under_review')
                    ->orderBy('updated_at')
                    ->limit(5))
                ->emptyStateHeading('No news in review')
                ->emptyStateDescription('Submitted news items will appear here.')
                ->columns([
                    $this->dashboardTitleColumn()
                        ->description(fn (News $record): string => collect([
                            $record->author?->name,
                            $record->category?->name,
                        ])->filter()->implode(' · ')),

                    $this->dashboardSinceColumn('updated_at', 'Updated'),
                ])
                ->recordUrl(fn (News $record): string => NewsResource::getUrl('edit', ['record' => $record]))
        );
    }
}
