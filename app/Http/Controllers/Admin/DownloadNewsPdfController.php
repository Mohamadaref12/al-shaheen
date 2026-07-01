<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\News\NewsPdfService;
use Illuminate\Http\Request;

class DownloadNewsPdfController extends Controller
{
    public function __invoke(Request $request, News $news)
    {
        $request->validate([
            'locale' => 'nullable|in:ar,en',
        ]);

        $locale = $request->input('locale', 'ar');

        $news->load(['author', 'category', 'translations']);

        if (! $news->translate($locale, false)) {
            abort(404, 'News translation not found for the requested locale.');
        }

        return app(NewsPdfService::class)->download($news, $locale);
    }
}
