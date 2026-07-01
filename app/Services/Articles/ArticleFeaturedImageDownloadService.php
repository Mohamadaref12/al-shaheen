<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Services\Media\ImageWatermarkService;
use App\Support\ImageStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ArticleFeaturedImageDownloadService
{
    public function __construct(
        private readonly ImageWatermarkService $watermarkService,
    ) {}

    public function download(Article $article, string $locale = 'ar'): BinaryFileResponse
    {
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';

        $translation = $article->translate($locale, false);

        if (! $translation) {
            abort(404, 'Article translation not found for the requested locale.');
        }

        if (blank($article->featured_image)) {
            abort(404, 'Article does not have a featured image.');
        }

        try {
            $watermarkedPath = $this->buildWatermarkedImage($article->featured_image);
        } catch (Throwable $e) {
            report($e);
            abort(422, 'Failed to prepare watermarked image.');
        }

        $baseName = Str::slug(
            Str::ascii($translation->slug ?: $translation->title ?: 'article-' . $article->id)
        ) ?: 'article-' . $article->id;

        $extension = strtolower(pathinfo($watermarkedPath, PATHINFO_EXTENSION) ?: 'jpg');
        $filename = $baseName . '-al-shaheen.' . $extension;

        return response()->download($watermarkedPath, $filename)->deleteFileAfterSend(true);
    }

    private function buildWatermarkedImage(string $featuredImage): string
    {
        if (str_starts_with($featuredImage, 'http://') || str_starts_with($featuredImage, 'https://')) {
            return $this->watermarkService->applyFromUrl($featuredImage);
        }

        $absolutePath = Storage::disk('images')->path($featuredImage);

        if (! file_exists($absolutePath)) {
            return $this->watermarkService->applyFromUrl(ImageStorage::url($featuredImage));
        }

        return $this->watermarkService->apply($absolutePath);
    }
}
