<?php

namespace App\Providers;

use App\Contracts\ArticleImprovementService;
use App\Services\Ai\NullArticleImprovementService;
use App\Services\Ai\OpenAiArticleImprovementService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleImprovementService::class, function (): ArticleImprovementService {
            if (config('ai.enabled') && filled(config('ai.openai.api_key'))) {
                return new OpenAiArticleImprovementService;
            }

            return new NullArticleImprovementService;
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
