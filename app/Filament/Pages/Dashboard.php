<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ArticlesPublishedChart;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\EditorialQueueWidget;
use App\Filament\Widgets\PendingCommentsWidget;
use App\Filament\Widgets\RecentArticlesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            ContentStatsOverview::class,
            ArticlesPublishedChart::class,
            EditorialQueueWidget::class,
            RecentArticlesWidget::class,
            PendingCommentsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
