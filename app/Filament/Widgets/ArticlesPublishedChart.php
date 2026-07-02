<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\News;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ArticlesPublishedChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Publishing activity';

    protected ?string $description = 'Articles and news published over time';

    protected ?string $maxHeight = '280px';

    protected function getFilters(): ?array
    {
        return [
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'Last 12 months',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? '30';

        return match ($filter) {
            '90' => $this->weeklyData(90),
            '365' => $this->monthlyData(12),
            default => $this->dailyData(30),
        };
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    private function dailyData(int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $labels[] = $date->format('M j');
            $data[] = $this->publishedCountBetween($date, $date->copy()->endOfDay());
        }

        return $this->chartPayload($labels, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function weeklyData(int $days): array
    {
        $labels = [];
        $data = [];
        $weeks = (int) ceil($days / 7);

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = $start->copy()->endOfWeek();
            $labels[] = $start->format('M j');
            $data[] = $this->publishedCountBetween($start, $end);
        }

        return $this->chartPayload($labels, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function monthlyData(int $months): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $labels[] = $start->format('M Y');
            $data[] = $this->publishedCountBetween($start, $end);
        }

        return $this->chartPayload($labels, $data);
    }

    private function publishedCountBetween(Carbon $start, Carbon $end): int
    {
        return Article::query()
            ->where('status', 'published')
            ->whereBetween('published_at', [$start, $end])
            ->count()
            + News::query()
                ->where('status', 'published')
                ->whereBetween('published_at', [$start, $end])
                ->count();
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int>  $data
     * @return array<string, mixed>
     */
    private function chartPayload(array $labels, array $data): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Published items',
                    'data' => $data,
                    'borderColor' => '#28414e',
                    'backgroundColor' => 'rgba(40, 65, 78, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
