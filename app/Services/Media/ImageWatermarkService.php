<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImageWatermarkService
{
    public function apply(string $sourcePath): string
    {
        if (! file_exists($sourcePath)) {
            throw new RuntimeException('Source image not found.');
        }

        $watermarkPath = $this->resolveWatermarkPath();

        if (! is_string($watermarkPath) || ! file_exists($watermarkPath)) {
            throw new RuntimeException(
                'Watermark image is not configured or missing. Upload public/brand/al-shaheen-watermark.png to the server or set BRAND_WATERMARK_PATH in .env'
            );
        }

        if (extension_loaded('imagick') && $this->shouldUseImagick()) {
            return $this->applyWithImagick($sourcePath, $watermarkPath);
        }

        if (extension_loaded('gd')) {
            return $this->applyWithGd($sourcePath, $watermarkPath);
        }

        if (extension_loaded('imagick')) {
            return $this->applyWithImagick($sourcePath, $watermarkPath);
        }

        throw new RuntimeException('No supported PHP image extension found. Install GD or Imagick.');
    }

    private function shouldUseImagick(): bool
    {
        return match (config('brand.watermark_driver', 'gd')) {
            'imagick' => true,
            'gd'      => false,
            default   => false,
        };
    }

    public function applyFromUrl(string $url): string
    {
        $response = Http::timeout(20)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to download remote image.');
        }

        $extension = $this->guessExtensionFromUrl($url) ?? 'jpg';
        $tempSource = $this->tempPath('source', $extension);

        file_put_contents($tempSource, $response->body());

        try {
            return $this->apply($tempSource);
        } finally {
            @unlink($tempSource);
        }
    }

    private function applyWithImagick(string $sourcePath, string $watermarkPath): string
    {
        $image = new \Imagick($sourcePath);
        $image->setImageColorspace(\Imagick::COLORSPACE_SRGB);
        $image->autoOrient();

        $watermark = new \Imagick($watermarkPath);
        $watermark->setImageFormat('png');
        $watermark->setImageColorspace(\Imagick::COLORSPACE_SRGB);
        $watermark->setImageBackgroundColor(new \ImagickPixel('transparent'));
        $watermark->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

        $this->removeLightBackground($watermark);

        $targetWidth = max(120, (int) round($image->getImageWidth() * (float) config('brand.width_ratio', 0.22)));
        $watermark->resizeImage($targetWidth, 0, \Imagick::FILTER_LANCZOS, 1);

        $opacity = max(0.25, min(1.0, (float) config('brand.opacity', 0.55)));
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);

        [$x, $y] = $this->resolvePosition(
            $image->getImageWidth(),
            $image->getImageHeight(),
            $watermark->getImageWidth(),
            $watermark->getImageHeight()
        );

        $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);

        $format = strtolower($image->getImageFormat() ?: 'jpeg');
        if (! in_array($format, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            $format = 'jpeg';
        }

        if (in_array($format, ['jpeg', 'jpg'], true)) {
            $image->setImageBackgroundColor(new \ImagickPixel('white'));
            $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }

        $output = $this->tempPath('watermarked', $format === 'jpg' ? 'jpeg' : $format);

        if (in_array($format, ['jpeg', 'jpg'], true)) {
            $image->setImageCompressionQuality(90);
        }

        $image->writeImage($output);

        $image->clear();
        $watermark->clear();

        return $output;
    }

    private function applyWithGd(string $sourcePath, string $watermarkPath): string
    {
        $source = $this->loadGdImage($sourcePath);
        $watermark = $this->prepareGdWatermark($watermarkPath, imagesx($source));

        imagesavealpha($source, true);
        imagealphablending($source, true);

        [$x, $y] = $this->resolvePosition(
            imagesx($source),
            imagesy($source),
            imagesx($watermark),
            imagesy($watermark)
        );

        $this->overlayGdImage($source, $watermark, $x, $y, (float) config('brand.opacity', 0.55));

        imagedestroy($watermark);

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg');
        $output = $this->tempPath('watermarked', $extension);

        match ($extension) {
            'png'  => imagepng($source, $output, 6),
            'webp' => imagewebp($source, $output, 90),
            'gif'  => imagegif($source, $output),
            default => imagejpeg($source, $output, 90),
        };

        imagedestroy($source);

        return $output;
    }

    private function prepareGdWatermark(string $watermarkPath, int $sourceWidth): \GdImage
    {
        $original = $this->loadGdImage($watermarkPath);

        imagealphablending($original, false);
        imagesavealpha($original, true);

        $this->removeLightBackgroundGd($original);

        $targetWidth = max(120, (int) round($sourceWidth * (float) config('brand.width_ratio', 0.22)));
        $originalWidth = imagesx($original);
        $originalHeight = imagesy($original);
        $targetHeight = (int) round($originalHeight * ($targetWidth / $originalWidth));

        $scaled = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);

        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefilledrectangle($scaled, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $scaled,
            $original,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $originalWidth,
            $originalHeight
        );

        imagedestroy($original);

        return $scaled;
    }

    private function overlayGdImage(\GdImage $destination, \GdImage $overlay, int $offsetX, int $offsetY, float $opacity): void
    {
        $overlayWidth = imagesx($overlay);
        $overlayHeight = imagesy($overlay);
        $opacity = max(0.0, min(1.0, $opacity));

        for ($x = 0; $x < $overlayWidth; $x++) {
            for ($y = 0; $y < $overlayHeight; $y++) {
                $overlayColor = imagecolorat($overlay, $x, $y);
                $alpha = ($overlayColor >> 24) & 0x7F;

                if ($alpha === 127) {
                    continue;
                }

                $destX = $offsetX + $x;
                $destY = $offsetY + $y;

                if ($destX < 0 || $destY < 0 || $destX >= imagesx($destination) || $destY >= imagesy($destination)) {
                    continue;
                }

                $srcAlpha = (1 - ($alpha / 127)) * $opacity;
                if ($srcAlpha <= 0) {
                    continue;
                }

                $srcR = ($overlayColor >> 16) & 0xFF;
                $srcG = ($overlayColor >> 8) & 0xFF;
                $srcB = $overlayColor & 0xFF;

                $destColor = imagecolorat($destination, $destX, $destY);
                $destR = ($destColor >> 16) & 0xFF;
                $destG = ($destColor >> 8) & 0xFF;
                $destB = $destColor & 0xFF;

                $blendedR = (int) round(($srcR * $srcAlpha) + ($destR * (1 - $srcAlpha)));
                $blendedG = (int) round(($srcG * $srcAlpha) + ($destG * (1 - $srcAlpha)));
                $blendedB = (int) round(($srcB * $srcAlpha) + ($destB * (1 - $srcAlpha)));

                $color = imagecolorallocate($destination, $blendedR, $blendedG, $blendedB);
                imagesetpixel($destination, $destX, $destY, $color);
            }
        }
    }

    private function removeLightBackground(\Imagick $image): void
    {
        $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

        $fuzz = \Imagick::getQuantum() * 0.12;

        $image->transparentPaintImage('#f8f8f5', 0, $fuzz, false);
        $image->transparentPaintImage('#ffffff', 0, $fuzz, false);
    }

    private function removeLightBackgroundGd(\GdImage $image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($r >= 228 && $g >= 228 && $b >= 220) {
                    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }
    }

    private function loadGdImage(string $path): \GdImage
    {
        $info = @getimagesize($path);

        if ($info === false) {
            throw new RuntimeException('Unsupported or unreadable image file.');
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            default        => null,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Unsupported image type.');
        }

        return $image;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolvePosition(int $canvasWidth, int $canvasHeight, int $overlayWidth, int $overlayHeight): array
    {
        $padding = (int) config('brand.padding', 24);
        $position = config('brand.position', 'bottom-right');

        return match ($position) {
            'bottom-center' => [
                (int) round(($canvasWidth - $overlayWidth) / 2),
                $canvasHeight - $overlayHeight - $padding,
            ],
            'center' => [
                (int) round(($canvasWidth - $overlayWidth) / 2),
                (int) round(($canvasHeight - $overlayHeight) / 2),
            ],
            default => [
                $canvasWidth - $overlayWidth - $padding,
                $canvasHeight - $overlayHeight - $padding,
            ],
        };
    }

    private function resolveWatermarkPath(): ?string
    {
        $candidates = array_filter(array_unique(array_merge(
            [config('brand.watermark_path')],
            config('brand.watermark_fallbacks', [])
        )));

        foreach ($candidates as $path) {
            if (is_string($path) && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function tempPath(string $prefix, string $extension): string
    {
        $directory = storage_path('app/temp/watermarks');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! is_writable($directory)) {
            throw new RuntimeException("Watermark temp directory is not writable: {$directory}");
        }

        return $directory . '/' . $prefix . '-' . Str::uuid() . '.' . ltrim($extension, '.');
    }

    private function guessExtensionFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $extension : null;
    }
}
