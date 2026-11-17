<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageOptimizationService
{
    /**
     * Supported image formats
     */
    const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Max file size (5MB)
     */
    const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Image sizes for different contexts
     */
    const IMAGE_SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'crop'],
        'small' => ['width' => 300, 'height' => 300, 'fit' => 'contain'],
        'medium' => ['width' => 600, 'height' => 600, 'fit' => 'contain'],
        'large' => ['width' => 1200, 'height' => 1200, 'fit' => 'contain'],
        'original' => ['width' => null, 'height' => null, 'fit' => 'original'],
    ];

    /**
     * Get Intervention Image Manager instance
     */
    private function getImageManager(): ImageManager
    {
        return new ImageManager(new Driver());
    }

    /**
     * Upload and optimize product image
     */
    public function uploadProductImage($file, int $productId, array $options = []): array
    {
        // Validate file
        $validation = $this->validateImage($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Generate unique filename
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->generateFilename($productId, $extension);

        // Store original
        $originalPath = "products/{$productId}/original/{$filename}";
        Storage::disk('public')->put($originalPath, file_get_contents($file));

        // Generate optimized versions
        $optimizedPaths = $this->generateOptimizedVersions($originalPath, $productId, $filename);

        // Determine primary path (WebP medium for web display)
        $primaryPath = $optimizedPaths['webp_medium'] ?? $optimizedPaths['medium'] ?? $originalPath;

        return [
            'success' => true,
            'original_path' => $originalPath,
            'primary_path' => $primaryPath,
            'optimized' => $optimizedPaths,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Validate uploaded image
     */
    private function validateImage($file): array
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => 'File size exceeds 5MB limit'
            ];
        }

        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return [
                'valid' => false,
                'error' => 'Invalid file type. Allowed: JPEG, PNG, WebP, GIF'
            ];
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::SUPPORTED_FORMATS)) {
            return [
                'valid' => false,
                'error' => 'Invalid file extension'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(int $productId, string $extension): string
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        return "product-{$productId}-{$timestamp}-{$random}.{$extension}";
    }

    /**
     * Generate optimized versions (WebP + multiple sizes)
     */
    private function generateOptimizedVersions(string $originalPath, int $productId, string $filename): array
    {
        $optimized = [];
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        try {
            $manager = $this->getImageManager();
            $image = $manager->read(Storage::disk('public')->path($originalPath));

            // Generate WebP versions (best for web)
            foreach (['thumbnail', 'small', 'medium', 'large'] as $sizeKey) {
                $size = self::IMAGE_SIZES[$sizeKey];
                $webpPath = "products/{$productId}/webp/{$baseName}-{$sizeKey}.webp";

                $this->resizeAndSave($manager, $image, $size, $webpPath, 'webp', 85);
                $optimized["webp_{$sizeKey}"] = $webpPath;
            }

            // Generate JPEG/PNG fallbacks
            foreach (['thumbnail', 'small', 'medium', 'large'] as $sizeKey) {
                $size = self::IMAGE_SIZES[$sizeKey];
                $fallbackPath = "products/{$productId}/optimized/{$baseName}-{$sizeKey}.{$extension}";

                $this->resizeAndSave($manager, $image, $size, $fallbackPath, $extension, 90);
                $optimized[$sizeKey] = $fallbackPath;
            }

            // Generate tiny blur placeholder (20px wide)
            $blurPath = "products/{$productId}/blur/{$baseName}-blur.webp";
            $this->resizeAndSave($manager, $image, ['width' => 20, 'height' => 20, 'fit' => 'crop'], $blurPath, 'webp', 20);
            $optimized['blur'] = $blurPath;

        } catch (\Exception $e) {
            Log::error("Image optimization failed: {$e->getMessage()}");
            // Return original path as fallback
            $optimized['original'] = $originalPath;
        }

        return $optimized;
    }

    /**
     * Resize and save image using Intervention Image v3
     */
    private function resizeAndSave(ImageManager $manager, $image, array $size, string $path, string $format, int $quality): void
    {
        $clone = clone $image;

        if ($size['fit'] === 'crop') {
            $clone = $clone->cover($size['width'], $size['height']);
        } elseif ($size['fit'] === 'contain') {
            $clone = $clone->scaleDown(width: $size['width'], height: $size['height']);
        }
        // 'original' - no resize

        // Ensure directory exists
        $fullPath = Storage::disk('public')->path($path);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save with optimization
        $encoded = match ($format) {
            'webp' => $clone->toWebp($quality),
            'jpg', 'jpeg' => $clone->toJpeg($quality),
            'png' => $clone->toPng(),
            default => $clone->toWebp($quality),
        };

        Storage::disk('public')->put($path, $encoded);
    }

    /**
     * Delete product images (all versions)
     */
    public function deleteProductImages(int $productId, string $filename = null): bool
    {
        try {
            if ($filename) {
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $dirs = ['original', 'webp', 'optimized', 'blur'];

                foreach ($dirs as $dir) {
                    $path = "products/{$productId}/{$dir}/";
                    if ($dir === 'original') {
                        Storage::disk('public')->delete("{$path}{$filename}");
                    } else {
                        // Delete all versions
                        $files = Storage::disk('public')->files($path);
                        foreach ($files as $file) {
                            if (str_contains($file, $baseName)) {
                                Storage::disk('public')->delete($file);
                            }
                        }
                    }
                }
            } else {
                // Delete entire product image directory
                Storage::disk('public')->deleteDirectory("products/{$productId}");
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete images: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get image URL for frontend
     */
    public function getImageUrl(int $productId, string $filename, string $variant = 'webp_medium'): string
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);

        $variantMap = [
            'thumbnail' => "products/{$productId}/webp/{$baseName}-thumbnail.webp",
            'small' => "products/{$productId}/webp/{$baseName}-small.webp",
            'medium' => "products/{$productId}/webp/{$baseName}-medium.webp",
            'large' => "products/{$productId}/webp/{$baseName}-large.webp",
            'webp_medium' => "products/{$productId}/webp/{$baseName}-medium.webp",
            'original' => "products/{$productId}/original/{$filename}",
            'blur' => "products/{$productId}/blur/{$baseName}-blur.webp",
        ];

        $path = $variantMap[$variant] ?? $variantMap['medium'];

        // Check if optimized version exists, fallback to original
        if (!Storage::disk('public')->exists($path)) {
            $path = "products/{$productId}/original/{$filename}";
        }

        return asset('storage/' . $path);
    }

    /**
     * Batch upload multiple images
     */
    public function uploadMultipleImages(array $files, int $productId): array
    {
        $results = [];

        foreach ($files as $index => $file) {
            $result = $this->uploadProductImage($file, $productId);
            $result['position'] = $index + 1;
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Generate optimized versions for existing image
     */
    public function generateOptimizedForExisting(int $productId, string $filename): array
    {
        $originalPath = "products/{$productId}/original/{$filename}";

        // Check if original exists
        if (!Storage::disk('public')->exists($originalPath)) {
            // Try to find the file in other locations
            $possiblePaths = [
                "products/{$filename}",
                "products/{$productId}/{$filename}",
            ];

            foreach ($possiblePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    // Copy to original location
                    $content = Storage::disk('public')->get($path);
                    Storage::disk('public')->put($originalPath, $content);
                    break;
                }
            }
        }

        if (!Storage::disk('public')->exists($originalPath)) {
            return ['success' => false, 'error' => 'Original file not found'];
        }

        try {
            $optimized = $this->generateOptimizedVersions($originalPath, $productId, $filename);
            return ['success' => true, 'optimized' => $optimized];
        } catch (\Exception $e) {
            Log::error("Failed to generate optimized versions: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}