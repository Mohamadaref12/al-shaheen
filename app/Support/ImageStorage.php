<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageStorage
{
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('images')->url($path);
    }
}
