<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Articles\ArticlePdfService;
use Illuminate\Http\Request;

class DownloadArticlePdfController extends Controller
{
    public function __invoke(Request $request, Article $article)
    {
        $request->validate([
            'locale' => 'nullable|in:ar,en',
        ]);

        $locale = $request->input('locale', 'ar');

        $article->load(['author', 'primaryCategory', 'tags', 'translations']);

        if (! $article->translate($locale, false)) {
            abort(404, 'Article translation not found for the requested locale.');
        }

        return app(ArticlePdfService::class)->download($article, $locale);
    }
}
