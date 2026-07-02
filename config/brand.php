<?php

return [
    'watermark_path' => env('BRAND_WATERMARK_PATH', public_path('brand/al-shaheen-watermark.png')),

    /*
    |--------------------------------------------------------------------------
    | Optional fallback paths (checked in order if watermark_path is missing)
    |--------------------------------------------------------------------------
    */
    'watermark_fallbacks' => [
        public_path('brand/al-shaheen-watermark.png'),
        public_path('brand/al-shaheen-watermark.jpg'),
        storage_path('app/brand/al-shaheen-watermark.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark width as a ratio of the source image width (0.1 - 0.5)
    |--------------------------------------------------------------------------
    */
    'width_ratio' => 0.22,

    /*
    |--------------------------------------------------------------------------
    | Opacity from 0 (invisible) to 1 (fully opaque)
    |--------------------------------------------------------------------------
    */
    'opacity' => 0.55,

    'padding' => 24,

    'position' => 'bottom-right',
];
