<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageAiSettings;
use App\Filament\Pages\ManageComingSoonSettings;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\News\NewsResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-actions';

    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label'       => __('filament.dashboard.actions.new_article.label'),
                    'description' => __('filament.dashboard.actions.new_article.description'),
                    'url'         => ArticleResource::getUrl('create'),
                    'icon'        => 'article',
                ],
                [
                    'label'       => __('filament.dashboard.actions.new_news.label'),
                    'description' => __('filament.dashboard.actions.new_news.description'),
                    'url'         => NewsResource::getUrl('create'),
                    'icon'        => 'news',
                ],
                [
                    'label'       => __('filament.dashboard.actions.review_articles.label'),
                    'description' => __('filament.dashboard.actions.review_articles.description'),
                    'url'         => ArticleResource::getUrl('index'),
                    'icon'        => 'queue',
                ],
                [
                    'label'       => __('filament.dashboard.actions.moderate_comments.label'),
                    'description' => __('filament.dashboard.actions.moderate_comments.description'),
                    'url'         => CommentResource::getUrl('index'),
                    'icon'        => 'comments',
                ],
                [
                    'label'       => __('filament.dashboard.actions.contact_inbox.label'),
                    'description' => __('filament.dashboard.actions.contact_inbox.description'),
                    'url'         => ContactMessageResource::getUrl('index'),
                    'icon'        => 'inbox',
                ],
                [
                    'label'       => __('filament.dashboard.actions.ai_settings.label'),
                    'description' => __('filament.dashboard.actions.ai_settings.description'),
                    'url'         => ManageAiSettings::getUrl(),
                    'icon'        => 'ai',
                ],
                [
                    'label'       => __('filament.dashboard.actions.coming_soon_settings.label'),
                    'description' => __('filament.dashboard.actions.coming_soon_settings.description'),
                    'url'         => ManageComingSoonSettings::getUrl(),
                    'icon'        => 'queue',
                ],
            ],
        ];
    }
}
