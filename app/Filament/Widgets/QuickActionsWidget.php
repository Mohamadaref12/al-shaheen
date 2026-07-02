<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageAiSettings;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Writers\WriterResource;
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
                    'label'       => 'New Article',
                    'description' => 'Create a bilingual story',
                    'url'         => ArticleResource::getUrl('create'),
                    'icon'        => 'article',
                ],
                [
                    'label'       => 'New News Item',
                    'description' => 'Publish breaking coverage',
                    'url'         => NewsResource::getUrl('create'),
                    'icon'        => 'news',
                ],
                [
                    'label'       => 'Review Articles',
                    'description' => 'Editorial queue',
                    'url'         => ArticleResource::getUrl('index'),
                    'icon'        => 'queue',
                ],
                [
                    'label'       => 'Moderate Comments',
                    'description' => 'Pending approvals',
                    'url'         => CommentResource::getUrl('index'),
                    'icon'        => 'comments',
                ],
                [
                    'label'       => 'Contact Inbox',
                    'description' => 'Reader messages',
                    'url'         => ContactMessageResource::getUrl('index'),
                    'icon'        => 'inbox',
                ],
                [
                    'label'       => 'AI Settings',
                    'description' => 'Translation & GPT',
                    'url'         => ManageAiSettings::getUrl(),
                    'icon'        => 'ai',
                ],
            ],
        ];
    }
}
