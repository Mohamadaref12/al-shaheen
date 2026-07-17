<?php

namespace App\Support;

use Mpdf\Mpdf;

class PdfBranding
{
    public static function makeMpdf(string $locale = 'en'): Mpdf
    {
        $fontOptions = PlatformFonts::mpdfOptions($locale);

        return new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'margin_left'      => 14,
            'margin_right'     => 14,
            'margin_top'       => 34,
            'margin_bottom'    => 24,
            'margin_header'    => 8,
            'margin_footer'    => 8,
            'tempDir'          => storage_path('app/mpdf'),
            ...$fontOptions,
        ]);
    }

    public static function apply(Mpdf $mpdf, string $locale, ?string $documentLabel = null): void
    {
        if ($locale === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->SetDefaultFont(PlatformFonts::mpdfFont($locale));

        $logoPath = self::logoPath();

        $mpdf->SetHTMLHeader(view('pdf.partials.header', [
            'locale'        => $locale,
            'logoPath'      => $logoPath,
            'documentLabel' => $documentLabel,
        ])->render());

        $mpdf->SetHTMLFooter(view('pdf.partials.footer', [
            'locale'   => $locale,
            'logoPath' => $logoPath,
        ])->render());
    }

    public static function logoPath(): ?string
    {
        $path = public_path('al-shaheen.png');

        return file_exists($path) ? $path : null;
    }
}
