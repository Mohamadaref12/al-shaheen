<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ArticlesPublishedChart;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\DashboardHeaderWidget;
use App\Filament\Widgets\EditorialQueueWidget;
use App\Filament\Widgets\NewsEditorialQueueWidget;
use App\Filament\Widgets\PendingCommentsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentArticlesWidget;
use App\Filament\Widgets\UnreadContactMessagesWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getWidgets(): array
    {
        return [
            DashboardHeaderWidget::class,
            QuickActionsWidget::class,
            ContentStatsOverview::class,
            ArticlesPublishedChart::class,
            EditorialQueueWidget::class,
            NewsEditorialQueueWidget::class,
            RecentArticlesWidget::class,
            PendingCommentsWidget::class,
            UnreadContactMessagesWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md'      => 2,
            'xl'      => 2,
        ];
    }
}
