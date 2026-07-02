<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\News;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-header';

    protected function getViewData(): array
    {
        $user = Auth::user();

        $attentionCount = Comment::query()->where('status', 'pending')->count()
            + ContactMessage::query()->unread()->count()
            + Article::query()->whereIn('status', ['submitted', 'under_review', 'review', 'ready', 'scheduled'])->count()
            + News::query()->where('status', 'under_review')->count();

        return [
            'userName'       => $user?->name,
            'dateLabel'      => now()->translatedFormat('l, F j, Y'),
            'attentionCount' => $attentionCount,
        ];
    }
}
