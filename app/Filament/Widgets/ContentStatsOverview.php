<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\News\NewsResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\News;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'At a glance';

    protected ?string $description = 'Key metrics across articles, news, and community';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 3,
            'xl'      => 4,
        ];
    }

    protected function getStats(): array
    {
        $publishedArticles = Article::query()->where('status', 'published')->count();
        $publishedNews = News::query()->where('status', 'published')->count();

        $articleQueueCount = Article::query()
            ->whereIn('status', ['submitted', 'under_review', 'review', 'ready', 'scheduled'])
            ->count();

        $newsQueueCount = News::query()->where('status', 'under_review')->count();

        $pendingCommentsCount = Comment::query()->where('status', 'pending')->count();
        $unreadContactCount = ContactMessage::query()->unread()->count();
        $needsAttention = $pendingCommentsCount + $unreadContactCount;

        $totalViews = (int) Article::query()->where('status', 'published')->sum('views_count')
            + (int) News::query()->where('status', 'published')->sum('views_count');

        $publishedTrend = $this->dailyPublishedCounts(7);

        return [
            Stat::make('Published', Number::format($publishedArticles + $publishedNews))
                ->description($publishedArticles.' articles · '.$publishedNews.' news')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('success')
                ->chart($publishedTrend)
                ->url(ArticleResource::getUrl('index')),

            Stat::make('Editorial Queue', Number::format($articleQueueCount + $newsQueueCount))
                ->description($articleQueueCount.' articles · '.$newsQueueCount.' news')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedInboxStack)
                ->color(($articleQueueCount + $newsQueueCount) > 0 ? 'warning' : 'gray')
                ->url(ArticleResource::getUrl('index')),

            Stat::make('Needs Attention', Number::format($needsAttention))
                ->description($pendingCommentsCount.' comments · '.$unreadContactCount.' messages')
                ->descriptionIcon(Heroicon::OutlinedBellAlert)
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color($needsAttention > 0 ? 'danger' : 'gray')
                ->url(CommentResource::getUrl('index')),

            Stat::make('Total Views', Number::abbreviate($totalViews))
                ->description('Across published content')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->icon(Heroicon::OutlinedChartBar)
                ->color('primary')
                ->chart($publishedTrend)
                ->url(NewsResource::getUrl('index')),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyPublishedCounts(int $days): array
    {
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $counts[] = Article::query()
                ->where('status', 'published')
                ->whereDate('published_at', $date)
                ->count()
                + News::query()
                    ->where('status', 'published')
                    ->whereDate('published_at', $date)
                    ->count();
        }

        return $counts;
    }
}
