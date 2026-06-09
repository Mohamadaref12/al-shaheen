<?php

namespace App\Http\Resources\Api\V1\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesImageUrls
{
    protected function imageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('images')->url($path);
    }
}
