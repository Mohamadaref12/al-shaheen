<?php

namespace App\Providers;

use App\Contracts\ArticleImprovementService;
use App\Contracts\ArticleTranslationService;
use App\Contracts\NewsImprovementService;
use App\Contracts\NewsTranslationService;
use App\Services\Ai\NullArticleImprovementService;
use App\Services\Ai\NullArticleTranslationService;
use App\Services\Ai\NullNewsImprovementService;
use App\Services\Ai\NullNewsTranslationService;
use App\Services\Ai\OpenAiArticleImprovementService;
use App\Services\Ai\OpenAiArticleTranslationService;
use App\Services\Ai\OpenAiNewsImprovementService;
use App\Services\Ai\OpenAiNewsTranslationService;
use App\Models\Article;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\ContentSubmission;
use App\Models\News;
use App\Models\Opinion;
use App\Models\Writer;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use App\Observers\ContactMessageObserver;
use App\Observers\ContentSubmissionObserver;
use App\Observers\NewsObserver;
use App\Observers\OpinionObserver;
use App\Observers\WriterObserver;
use App\Support\AiSettings;
use BezhanSalleh\LanguageSwitch\Enums\ItemStyle;
use BezhanSalleh\LanguageSwitch\Enums\TriggerStyle;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleImprovementService::class, function (): ArticleImprovementService {
            if (AiSettings::isConfigured()) {
                return new OpenAiArticleImprovementService;
            }

            return new NullArticleImprovementService;
        });

        $this->app->bind(ArticleTranslationService::class, function (): ArticleTranslationService {
            if (AiSettings::isConfigured()) {
                return new OpenAiArticleTranslationService;
            }

            return new NullArticleTranslationService;
        });

        $this->app->bind(NewsTranslationService::class, function (): NewsTranslationService {
            if (AiSettings::isConfigured()) {
                return new OpenAiNewsTranslationService;
            }

            return new NullNewsTranslationService;
        });

        $this->app->bind(NewsImprovementService::class, function (): NewsImprovementService {
            if (AiSettings::isConfigured()) {
                return new OpenAiNewsImprovementService;
            }

            return new NullNewsImprovementService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->locales(['en', 'ar'])
                ->flags([
                    'en' => url('images/flags/us.svg'),
                    'ar' => url('images/flags/ae.svg'),
                ])
                ->labels([
                    'en' => 'English',
                    'ar' => 'العربية',
                ])
                ->itemStyle(ItemStyle::FlagWithLabel)
                ->trigger(style: TriggerStyle::Flag)
                ->circular(false)
                ->userPreferredLocale(fn () => auth()->user()?->locale);
        });

        Event::listen(LocaleChanged::class, function (LocaleChanged $event): void {
            auth()->user()?->update(['locale' => $event->locale]);
        });

        Comment::observe(CommentObserver::class);
        ContactMessage::observe(ContactMessageObserver::class);
        Article::observe(ArticleObserver::class);
        News::observe(NewsObserver::class);
        Opinion::observe(OpinionObserver::class);
        Writer::observe(WriterObserver::class);
        ContentSubmission::observe(ContentSubmissionObserver::class);
    }
}
