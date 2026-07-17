<?php

namespace App\Support;

class PlatformFonts
{
    public const EN_FAMILY = "'Alexandria', ui-sans-serif, system-ui, sans-serif";

    public const AR_FAMILY = "'Alexandria', 'Century Gothic', ui-sans-serif, sans-serif";

    public const EN_NAME = 'Alexandria';

    public const AR_NAME = 'Alexandria';

    public static function cssFamily(string $locale): string
    {
        return $locale === 'ar' ? self::AR_FAMILY : self::EN_FAMILY;
    }

    /**
     * Alexandria cannot apply Arabic letter-joining in mPDF (OTL/GPOS incompatibility).
     * XB Riyaz is mPDF's bundled Arabic font with proper shaping.
     */
    public static function mpdfFont(string $locale): string
    {
        return $locale === 'ar' ? 'xbriyaz' : 'alexandria';
    }

    /**
     * @return array<string, mixed>
     */
    public static function mpdfOptions(string $locale): array
    {
        $defaults = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDefaults = (new \Mpdf\Config\FontVariables())->getDefaults();

        $fontDir = public_path('fonts/al-shaheen');

        return [
            'fontDir'  => array_merge($defaults['fontDir'], [$fontDir]),
            'fontdata' => array_merge($fontDefaults['fontdata'], [
                'alexandria' => [
                    'R' => 'Alexandria-Variable.ttf',
                ],
                'centurygothic' => [
                    'R'  => 'century-gothic/CenturyGothicPaneuropeanRegular.ttf',
                    'B'  => 'century-gothic/CenturyGothicPaneuropeanBold.ttf',
                    'I'  => 'century-gothic/CenturyGothicPaneuropeanItalic.ttf',
                    'BI' => 'century-gothic/CenturyGothicPaneuropeanBoldItalic.ttf',
                ],
            ]),
            'default_font' => self::mpdfFont($locale),
        ];
    }
}
