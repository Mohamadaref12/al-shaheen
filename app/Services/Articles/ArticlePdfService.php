<?php

namespace App\Services\Articles;

use App\Models\Article;
use App\Support\ImageStorage;
use App\Support\PdfBranding;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ArticlePdfService
{
    public function download(Article $article, string $locale = 'ar'): Response
    {
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';

        $translation = $article->translate($locale, false);

        if (! $translation) {
            abort(404, 'Article translation not found for the requested locale.');
        }

        $article->loadMissing(['author', 'primaryCategory', 'tags']);

        $html = view('pdf.article', [
            'article'           => $article,
            'translation'       => $translation,
            'locale'            => $locale,
            'featuredImagePath' => $this->resolveFeaturedImagePath($article),
        ])->render();

        $mpdf = PdfBranding::makeMpdf($locale);
        PdfBranding::apply(
            $mpdf,
            $locale,
            $locale === 'ar' ? 'مقال' : 'Article'
        );

        $mpdf->WriteHTML($html);

        $baseName = Str::slug(
            Str::ascii($translation->slug ?: $translation->title ?: 'article-' . $article->id)
        ) ?: 'article-' . $article->id;

        $filename = $baseName . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveFeaturedImagePath(Article $article): ?string
    {
        if (blank($article->featured_image)) {
            return null;
        }

        if (str_starts_with($article->featured_image, 'http://') || str_starts_with($article->featured_image, 'https://')) {
            return $article->featured_image;
        }

        $absolutePath = Storage::disk('images')->path($article->featured_image);

        return file_exists($absolutePath) ? $absolutePath : ImageStorage::url($article->featured_image);
    }
}
