<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Widgets\Concerns\ConfiguresDashboardTable;
use App\Models\Article;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentArticlesWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    use ConfiguresDashboardTable;

    protected static bool $isLazy = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $this->configureDashboardTable(
            $table
                ->heading('Recently Published')
                ->description('Latest live articles')
                ->query(fn (): Builder => Article::query()
                    ->with(['primaryCategory' => fn ($query) => $query->with('translations'), 'translations'])
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->limit(5))
                ->columns([
                    $this->dashboardTitleColumn(),

                    TextColumn::make('primaryCategory.name')
                        ->label('Category')
                        ->badge()
                        ->color('gray')
                        ->grow(false),

                    TextColumn::make('views_count')
                        ->label('Views')
                        ->icon(Heroicon::OutlinedEye)
                        ->numeric()
                        ->size(TextSize::Small)
                        ->grow(false)
                        ->alignEnd(),

                    $this->dashboardSinceColumn('published_at', 'Published'),
                ])
                ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('view', ['record' => $record]))
        );
    }
}
