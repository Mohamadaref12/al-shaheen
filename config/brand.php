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
    |
    | Use a high-resolution transparent PNG (ideally 1500px+ wide). A small
    | source file will look pixelated if upscaled onto large photos.
    |
    */
    'width_ratio' => 0.22,

    /*
    |--------------------------------------------------------------------------
    | Never scale the watermark larger than its native pixel size
    |--------------------------------------------------------------------------
    |
    | Keeps edges sharp. Set false only if you intentionally want upscaling
    | from a small asset (will look soft/pixelated).
    |
    */
    'prevent_upscale' => true,

    /*
    |--------------------------------------------------------------------------
    | Opacity from 0 (invisible) to 1 (fully opaque)
    |--------------------------------------------------------------------------
    */
    'opacity' => 0.55,

    'padding' => 24,

    'position' => 'bottom-right',

    /*
    |--------------------------------------------------------------------------
    | Output JPEG quality (1-100) for watermarked downloads
    |--------------------------------------------------------------------------
    */
    'output_quality' => 95,

    /*
    |--------------------------------------------------------------------------
    | Image driver: gd (recommended), imagick, or auto
    |--------------------------------------------------------------------------
    */
    'watermark_driver' => env('BRAND_WATERMARK_DRIVER', 'gd'),

    'fonts' => [
        'english' => public_path('fonts/al-shaheen/Alexandria-Variable.ttf'),
        'arabic'  => public_path('fonts/al-shaheen/Alexandria-Variable.ttf'),
    ],
];
