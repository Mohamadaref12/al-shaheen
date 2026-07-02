<?php

namespace App\Providers;

use App\Contracts\ArticleImprovementService;
use App\Contracts\ArticleTranslationService;
use App\Services\Ai\NullArticleImprovementService;
use App\Services\Ai\NullArticleTranslationService;
use App\Services\Ai\OpenAiArticleImprovementService;
use App\Services\Ai\OpenAiArticleTranslationService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
