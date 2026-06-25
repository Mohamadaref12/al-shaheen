<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\Writers\WriterResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\Writer;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Newsroom Overview';

    protected function getStats(): array
    {
        $publishedCount = Article::query()->where('status', 'published')->count();

        $editorialQueueCount = Article::query()
            ->whereIn('status', ['submitted', 'under_review', 'review', 'ready', 'scheduled'])
            ->count();

        $pendingCommentsCount = Comment::query()->where('status', 'pending')->count();

        $totalViews = (int) Article::query()->where('status', 'published')->sum('views_count');

        $pendingWriterApps = Writer::query()
            ->whereIn('application_status', ['submitted', 'under_review'])
            ->count();

        $activeSubscriptions = Subscription::query()->where('status', 'active')->count();

        $publishedTrend = $this->dailyPublishedCounts(7);

        return [
            Stat::make('Published Articles', Number::format($publishedCount))
                ->description('Live on the platform')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('success')
                ->chart($publishedTrend)
                ->url(ArticleResource::getUrl('index')),

            Stat::make('Editorial Queue', Number::format($editorialQueueCount))
                ->description('Awaiting review or scheduling')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedInboxStack)
                ->color($editorialQueueCount > 0 ? 'warning' : 'gray')
                ->url(ArticleResource::getUrl('index')),

            Stat::make('Pending Comments', Number::format($pendingCommentsCount))
                ->description('Need moderation')
                ->descriptionIcon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color($pendingCommentsCount > 0 ? 'warning' : 'gray')
                ->url(CommentResource::getUrl('index')),

            Stat::make('Article Views', Number::abbreviate($totalViews))
                ->description('Total on published articles')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->icon(Heroicon::OutlinedChartBar)
                ->color('primary')
                ->chart($publishedTrend),

            Stat::make('Writer Applications', Number::format($pendingWriterApps))
                ->description('Submitted or under review')
                ->descriptionIcon(Heroicon::OutlinedUserPlus)
                ->icon(Heroicon::OutlinedIdentification)
                ->color($pendingWriterApps > 0 ? 'info' : 'gray')
                ->url(WriterResource::getUrl('index')),

            Stat::make('Active Subscriptions', Number::format($activeSubscriptions))
                ->description('Paying subscribers')
                ->descriptionIcon(Heroicon::OutlinedCreditCard)
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyPublishedCounts(int $days): array
    {
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $counts[] = Article::query()
                ->where('status', 'published')
                ->whereDate('published_at', now()->subDays($i))
                ->count();
        }

        return $counts;
    }
}
