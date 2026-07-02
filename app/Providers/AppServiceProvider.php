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
use App\Support\AiSettings;
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
        //
    }
}
