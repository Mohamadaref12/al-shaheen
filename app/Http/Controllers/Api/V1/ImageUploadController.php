<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UploadImageRequest;
use App\Traits\OptimizesImages;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageUploadController extends Controller
{
    use OptimizesImages;

    public function store(UploadImageRequest $request): JsonResponse
    {
        try {
            $type = $request->validated('type');

            [$directory, $maxWidth] = match ($type) {
                'profile'   => ['uploads/profiles', 800],
                'featured'  => ['uploads/featured', 1920],
                'news'      => ['uploads/news', 1920],
                'portfolio' => ['uploads/portfolio', 2048],
                'general'   => ['uploads/general', 1920],
            };

            $disk = 'images';

            $path = $this->storeOptimizedImage(
                $request->file('image'),
                $directory,
                $maxWidth,
                85,
                $disk
            );

            return $this->success([
                'type' => $type,
                'path' => $path,
                'url'  => Storage::disk($disk)->url($path),
            ], 'Image uploaded successfully.', 201);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'No supported PHP image extension')) {
                report($e);

                return $this->error(
                    null,
                    'Image processing is unavailable on the server. Install and enable PHP GD or Imagick.',
                    503
                );
            }

            return $this->handleException($e, 'Failed to upload image.');
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Permission denied')
                || str_contains($e->getMessage(), 'failed to open stream')) {
                report($e);

                return $this->error(
                    null,
                    'Server cannot write uploaded files. Check storage directory permissions.',
                    500
                );
            }

            return $this->handleException($e, 'Failed to upload image.');
        }
    }
}
