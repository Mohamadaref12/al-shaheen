<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * Provides image URL accessors for models that store image paths.
 *
 * Automatically appends `_url` fields based on the disk 'images'.
 * Works with APP_URL — resolves correctly on local and production.
 */
trait HasImageUrl
{
    /**
     * Get full URL for an image path on the 'images' disk.
     */
    protected function getImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('images')->url($path);
    }
}
