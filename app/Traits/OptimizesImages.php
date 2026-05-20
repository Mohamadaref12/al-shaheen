<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\Image\Enums\ImageDriver as SpatieImageDriver;
use Spatie\Image\Enums\Fit;

trait OptimizesImages
{
    /**
     * Store and optimize an image file, converting to WebP format
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth Maximum width in pixels (default: 1920)
     * @param int $quality Quality for WebP (0-100, default: 85)
     * @return string The stored file path
     */
    protected function storeOptimizedImage(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $quality = 85,
        string $disk = 'images'
    ): string {
        // Generate unique filename with webp extension
        $filename = Str::uuid() . '.webp';
        $storagePath = $directory . '/' . $filename;
        $fullPath = Storage::disk($disk)->path($storagePath);

        // Ensure directory exists
        $directoryPath = dirname($fullPath);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        // Load and optimize the image
        $this->makeImageEditor($file->getPathname())
            ->fit(Fit::Max, $maxWidth, $maxWidth) // Resize if larger than max
            ->format('webp')
            ->quality($quality)
            ->save($fullPath);

        return $storagePath;
    }

    /**
     * Store and optimize an image with thumbnail generation
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth Maximum width for main image
     * @param int $thumbWidth Thumbnail width (default: 300)
     * @param int $quality Quality for WebP (0-100)
     * @return array ['original' => path, 'thumbnail' => path]
     */
    protected function storeOptimizedImageWithThumbnail(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $thumbWidth = 300,
        int $quality = 85,
        string $disk = 'images'
    ): array {
        $uuid = Str::uuid();
        
        // Store original optimized image
        $originalFilename = $uuid . '.webp';
        $originalPath = $directory . '/' . $originalFilename;
        $originalFullPath = Storage::disk($disk)->path($originalPath);

        // Store thumbnail
        $thumbFilename = $uuid . '_thumb.webp';
        $thumbPath = $directory . '/' . $thumbFilename;
        $thumbFullPath = Storage::disk($disk)->path($thumbPath);

        // Ensure directory exists
        $directoryPath = dirname($originalFullPath);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        // Save original optimized
        $this->makeImageEditor($file->getPathname())
            ->fit(Fit::Max, $maxWidth, $maxWidth)
            ->format('webp')
            ->quality($quality)
            ->save($originalFullPath);

        // Save thumbnail
        $this->makeImageEditor($file->getPathname())
            ->fit(Fit::Crop, $thumbWidth, $thumbWidth)
            ->format('webp')
            ->quality($quality)
            ->save($thumbFullPath);

        return [
            'original' => $originalPath,
            'thumbnail' => $thumbPath,
        ];
    }

    /**
     * Store portfolio image (async optimization, max 2048px)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string The stored file path (.webp)
     */
    protected function storePortfolioImage(UploadedFile $file, string $directory, string $disk = 'images'): string
    {
        return $this->storeImageForAsyncOptimization($file, $directory, 2048, 90, $disk);
    }

    /**
     * Store profile photo (async optimization)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string The stored file path (.webp)
     */
    protected function storeProfilePhoto(UploadedFile $file, string $directory, string $disk = 'images'): string
    {
        return $this->storeImageForAsyncOptimization($file, $directory, 1024, 85, $disk);
    }

    /**
     * Store profession media photo (async optimization, max 1024px)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string The stored file path (.webp)
     */
    protected function storeProfessionPhoto(UploadedFile $file, string $directory, string $disk = 'images'): string
    {
        return $this->storeImageForAsyncOptimization($file, $directory, 1024, 85, $disk);
    }

    /**
     * Store image temporarily and dispatch optimization job
     * Returns WebP path immediately, optimization happens in background
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth
     * @param int $quality
     * @return string The WebP file path that will exist after optimization
     */
    protected function storeImageForAsyncOptimization(UploadedFile $file, string $directory, int $maxWidth = 1920, int $quality = 85, string $disk = 'images'): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
        $fileName = Str::uuid()->toString() . '.' . strtolower($extension);
        
        // Store original file first (fast response)
        $filePath = $file->storeAs($directory, $fileName, $disk);
        
        // Dispatch job to optimize in background (converts to WebP and deletes original)
        $optimizeJobClass = 'App\\Jobs\\OptimizeImageJob';

        if (class_exists($optimizeJobClass)) {
            $optimizeJobClass::dispatch($filePath, $maxWidth, $quality, true, $disk);
        }
        
        // Return the WebP path that will exist after optimization
        $webpPath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.webp';
        
        return $webpPath;
    }

    /**
     * Batch optimize existing images in storage
     * Useful for migrating existing images to WebP
     *
     * @param string $directory
     * @param array $extensions Image extensions to convert (default: jpg, jpeg, png)
     * @return int Number of images converted
     */
    protected function batchOptimizeImages(
        string $directory,
        array $extensions = ['jpg', 'jpeg', 'png'],
        string $diskName = 'images'
    ): int {
        $disk = Storage::disk($diskName);
        $files = $disk->allFiles($directory);
        $converted = 0;

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $extensions)) {
                continue;
            }

            try {
                $fullPath = $disk->path($file);
                $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);
                $newFullPath = $disk->path($newPath);

                // Convert to WebP
                $this->makeImageEditor($fullPath)
                    ->format('webp')
                    ->quality(85)
                    ->save($newFullPath);

                // Delete old file
                $disk->delete($file);
                $converted++;
            } catch (\Exception $e) {
                // Log error but continue processing
                Log::error("Failed to convert image: {$file}", ['error' => $e->getMessage()]);
            }
        }

        return $converted;
    }

    protected function makeImageEditor(string $path): Image
    {
        if (extension_loaded('imagick')) {
            return Image::useImageDriver(SpatieImageDriver::Imagick)->loadFile($path);
        }

        if (extension_loaded('gd')) {
            return Image::useImageDriver(SpatieImageDriver::Gd)->loadFile($path);
        }

        throw new \RuntimeException('No supported PHP image extension found. Install Imagick or GD.');
    }
}
