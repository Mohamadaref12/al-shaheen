<?php

namespace App\Services\News;

use App\Models\News;
use App\Support\ImageStorage;
use App\Support\PdfBranding;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class NewsPdfService
{
    public function download(News $news, string $locale = 'ar'): Response
    {
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';

        $translation = $news->translate($locale, false);

        if (! $translation) {
            abort(404, 'News translation not found for the requested locale.');
        }

        $news->loadMissing(['author', 'category']);

        $html = view('pdf.news', [
            'news'              => $news,
            'translation'       => $translation,
            'locale'            => $locale,
            'featuredImagePath' => $this->resolveFeaturedImagePath($news),
        ])->render();

        $mpdf = PdfBranding::makeMpdf($locale);
        PdfBranding::apply(
            $mpdf,
            $locale,
            $locale === 'ar' ? 'خبر' : 'News'
        );

        $mpdf->WriteHTML($html);

        $baseName = Str::slug(
            Str::ascii($translation->slug ?: $translation->title ?: 'news-' . $news->id)
        ) ?: 'news-' . $news->id;

        $filename = $baseName . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveFeaturedImagePath(News $news): ?string
    {
        if (blank($news->featured_image)) {
            return null;
        }

        if (str_starts_with($news->featured_image, 'http://') || str_starts_with($news->featured_image, 'https://')) {
            return $news->featured_image;
        }

        $absolutePath = Storage::disk('images')->path($news->featured_image);

        return file_exists($absolutePath) ? $absolutePath : ImageStorage::url($news->featured_image);
    }
}
