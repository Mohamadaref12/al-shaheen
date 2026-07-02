<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Services\Media\ImageWatermarkService;
use App\Support\ImageStorage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ArticleFeaturedImageDownloadService
{
    public function __construct(
        private readonly ImageWatermarkService $watermarkService,
    ) {}

    public function download(Article $article, string $locale = 'ar', bool $inline = false): BinaryFileResponse
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
            Log::error('Watermarked image generation failed', [
                'article_id'      => $article->id,
                'featured_image'  => $article->featured_image,
                'watermark_path'  => config('brand.watermark_path'),
                'watermark_driver'=> config('brand.watermark_driver', 'gd'),
                'gd_loaded'       => extension_loaded('gd'),
                'imagick_loaded'  => extension_loaded('imagick'),
                'error'           => $e->getMessage(),
            ]);

            report($e);
            abort(422, 'Failed to prepare watermarked image.');
        }

        $baseName = Str::slug(
            Str::ascii($translation->slug ?: $translation->title ?: 'article-' . $article->id)
        ) ?: 'article-' . $article->id;

        $extension = strtolower(pathinfo($watermarkedPath, PATHINFO_EXTENSION) ?: 'jpg');
        $filename = $baseName . '-al-shaheen.' . $extension;
        $mimeType = match ($extension) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            default => 'image/jpeg',
        };

        $headers = [
            'Content-Type'         => $mimeType,
            'X-Watermark-Applied'  => 'true',
            'X-Watermark-Driver'   => (string) config('brand.watermark_driver', 'gd'),
        ];

        if ($inline) {
            return response()->file($watermarkedPath, $headers)->deleteFileAfterSend(true);
        }

        return response()->download($watermarkedPath, $filename, $headers)->deleteFileAfterSend(true);
    }

    private function buildWatermarkedImage(string $featuredImage): string
    {
        if (str_starts_with($featuredImage, 'http://') || str_starts_with($featuredImage, 'https://')) {
            return $this->watermarkService->applyFromUrl($featuredImage);
        }

        $absolutePath = Storage::disk('images')->path($featuredImage);

        if (file_exists($absolutePath)) {
            return $this->watermarkService->apply($absolutePath);
        }

        return $this->watermarkService->applyFromUrl(ImageStorage::url($featuredImage));
    }
}
