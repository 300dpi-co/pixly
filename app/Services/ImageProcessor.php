<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Image Processor
 *
 * Handles image resizing, thumbnails, and WebP conversion.
 */
class ImageProcessor
{
    private string $uploadPath;

    // Image sizes configuration
    private array $sizes = [
        'thumbnail' => ['width' => 300, 'height' => 300, 'crop' => true],
        'medium' => ['width' => 800, 'height' => 800, 'crop' => false],
        'large' => ['width' => 1920, 'height' => 1920, 'crop' => false],
    ];

    // WebP quality (0-100)
    private int $webpQuality = 82;

    // JPEG quality for fallback
    private int $jpegQuality = 85;

    public function __construct(?string $uploadPath = null)
    {
        $this->uploadPath = $uploadPath ?? \ROOT_PATH . '/uploads';
    }

    /**
     * Check if a GIF file is animated (has multiple frames)
     */
    public function isAnimatedGif(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        // Check if it's a GIF
        $info = getimagesize($path);
        if (!$info || $info[2] !== IMAGETYPE_GIF) {
            return false;
        }

        // Read file and look for multiple frame markers
        $content = file_get_contents($path);

        // Count graphic control extension blocks (indicates frames)
        // Each frame in an animated GIF has a graphic control extension (0x21 0xF9)
        $count = preg_match_all('/\x00\x21\xF9\x04/', $content, $matches);

        return $count > 1;
    }

    /**
     * Check if file is a GIF
     */
    public function isGif(string $path): bool
    {
        $info = getimagesize($path);
        return $info && $info[2] === IMAGETYPE_GIF;
    }

    /**
     * Check if WebP is supported
     */
    public static function webpSupported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromwebp');
    }

    /**
     * Process an uploaded image - create thumbnails and WebP versions
     */
    public function process(string $sourcePath, string $subdir = 'images'): array
    {
        $absolutePath = $this->uploadPath . '/' . $sourcePath;

        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("Source file not found: {$absolutePath}");
        }

        $pathInfo = pathinfo($absolutePath);
        $baseDir = dirname($sourcePath);

        $results = [
            'original' => $sourcePath,
            'thumbnail' => null,
            'thumbnail_webp' => null,
            'medium' => null,
            'medium_webp' => null,
            'webp' => null,
            'is_animated' => false,
        ];

        // Check if this is an animated GIF
        $isAnimatedGif = $this->isAnimatedGif($absolutePath);
        $results['is_animated'] = $isAnimatedGif;

        if ($isAnimatedGif) {
            // For animated GIFs: create static thumbnail only, skip WebP conversion
            // The original GIF will be served for full view to preserve animation

            // Create static thumbnail (first frame as JPG)
            $thumbnailPath = $this->createThumbnail($absolutePath, $baseDir, $pathInfo['filename']);
            if ($thumbnailPath) {
                $results['thumbnail'] = $thumbnailPath;
            }

            // Extract colors from first frame
            $results['dominant_color'] = $this->extractDominantColor($absolutePath);
            $results['color_palette'] = $this->extractColorPalette($absolutePath);

            return $results;
        }

        // Standard processing for non-animated images

        // Create thumbnail (JPG)
        $thumbnailPath = $this->createThumbnail($absolutePath, $baseDir, $pathInfo['filename']);
        if ($thumbnailPath) {
            $results['thumbnail'] = $thumbnailPath;

            // Create WebP version of thumbnail
            $thumbAbsolute = $this->uploadPath . '/' . $thumbnailPath;
            $thumbWebp = $this->createWebP($thumbAbsolute, $baseDir, $pathInfo['filename'] . '_thumb');
            if ($thumbWebp) {
                $results['thumbnail_webp'] = $thumbWebp;
            }
        }

        // Create medium size (for gallery view)
        $mediumPath = $this->createSize($absolutePath, $baseDir, $pathInfo['filename'], 'medium');
        if ($mediumPath) {
            $results['medium'] = $mediumPath;

            // Create WebP version of medium
            $mediumAbsolute = $this->uploadPath . '/' . $mediumPath;
            $mediumWebp = $this->createWebP($mediumAbsolute, $baseDir, $pathInfo['filename'] . '_medium');
            if ($mediumWebp) {
                $results['medium_webp'] = $mediumWebp;
            }
        }

        // Create WebP version of original (for full-size view)
        $webpPath = $this->createWebP($absolutePath, $baseDir, $pathInfo['filename']);
        if ($webpPath) {
            $results['webp'] = $webpPath;
        }

        // Extract dominant color
        $results['dominant_color'] = $this->extractDominantColor($absolutePath);

        // Extract color palette
        $results['color_palette'] = $this->extractColorPalette($absolutePath);

        return $results;
    }

    /**
     * Create thumbnail from image
     */
    public function createThumbnail(string $sourcePath, string $relativeDir, string $filename): ?string
    {
        $size = $this->sizes['thumbnail'];
        $outputFilename = $filename . '_thumb.jpg';
        $outputPath = $this->uploadPath . '/' . $relativeDir . '/' . $outputFilename;

        if ($this->resize($sourcePath, $outputPath, $size['width'], $size['height'], $size['crop'] ?? true)) {
            return $relativeDir . '/' . $outputFilename;
        }

        return null;
    }

    /**
     * Create a specific size variant
     */
    public function createSize(string $sourcePath, string $relativeDir, string $filename, string $sizeName): ?string
    {
        if (!isset($this->sizes[$sizeName])) {
            return null;
        }

        $size = $this->sizes[$sizeName];
        $outputFilename = $filename . '_' . $sizeName . '.jpg';
        $outputPath = $this->uploadPath . '/' . $relativeDir . '/' . $outputFilename;

        if ($this->resize($sourcePath, $outputPath, $size['width'], $size['height'], $size['crop'] ?? false)) {
            return $relativeDir . '/' . $outputFilename;
        }

        return null;
    }

    /**
     * Create WebP version of image
     */
    public function createWebP(string $sourcePath, string $relativeDir, string $filename): ?string
    {
        if (!self::webpSupported()) {
            return null;
        }

        $outputFilename = $filename . '.webp';
        $outputPath = $this->uploadPath . '/' . $relativeDir . '/' . $outputFilename;

        $image = $this->loadImage($sourcePath);
        if (!$image) {
            return null;
        }

        // Preserve transparency for PNG
        imagesavealpha($image, true);

        // Convert to WebP
        if (imagewebp($image, $outputPath, $this->webpQuality)) {
            imagedestroy($image);
            return $relativeDir . '/' . $outputFilename;
        }

        imagedestroy($image);
        return null;
    }

    /**
     * Create WebP from any image path (for batch conversion)
     */
    public function convertToWebP(string $relativePath): ?string
    {
        $absolutePath = $this->uploadPath . '/' . $relativePath;

        if (!file_exists($absolutePath)) {
            return null;
        }

        $pathInfo = pathinfo($absolutePath);
        $relativeDir = dirname($relativePath);

        return $this->createWebP($absolutePath, $relativeDir, $pathInfo['filename']);
    }

    /**
     * Generate all WebP variants for an existing image
     */
    public function generateWebPVariants(array $image): array
    {
        $results = [];

        // Original WebP
        if (!empty($image['storage_path']) && empty($image['webp_path'])) {
            $webp = $this->convertToWebP($image['storage_path']);
            if ($webp) {
                $results['webp_path'] = $webp;
            }
        }

        // Thumbnail WebP
        if (!empty($image['thumbnail_path']) && empty($image['thumbnail_webp_path'])) {
            $thumbWebp = $this->convertToWebP($image['thumbnail_path']);
            if ($thumbWebp) {
                $results['thumbnail_webp_path'] = $thumbWebp;
            }
        }

        return $results;
    }

    /**
     * Resize an image
     */
    public function resize(
        string $sourcePath,
        string $outputPath,
        int $maxWidth,
        int $maxHeight,
        bool $crop = false
    ): bool {
        $image = $this->loadImage($sourcePath);
        if (!$image) {
            return false;
        }

        $srcWidth = imagesx($image);
        $srcHeight = imagesy($image);

        if ($crop) {
            // Crop to fill dimensions
            $ratio = max($maxWidth / $srcWidth, $maxHeight / $srcHeight);
            $newWidth = (int) ($srcWidth * $ratio);
            $newHeight = (int) ($srcHeight * $ratio);
            $offsetX = (int) (($newWidth - $maxWidth) / 2);
            $offsetY = (int) (($newHeight - $maxHeight) / 2);

            $resized = imagecreatetruecolor($maxWidth, $maxHeight);
            $this->preserveTransparency($resized, $sourcePath);

            // First resize to fit
            $temp = imagecreatetruecolor($newWidth, $newHeight);
            $this->preserveTransparency($temp, $sourcePath);
            imagecopyresampled($temp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

            // Then crop to exact dimensions
            imagecopy($resized, $temp, 0, 0, $offsetX, $offsetY, $maxWidth, $maxHeight);
            imagedestroy($temp);
        } else {
            // Resize to fit within dimensions
            $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);

            // Don't upscale
            if ($ratio >= 1) {
                imagedestroy($image);
                return copy($sourcePath, $outputPath);
            }

            $newWidth = (int) ($srcWidth * $ratio);
            $newHeight = (int) ($srcHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            $this->preserveTransparency($resized, $sourcePath);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        }

        // Save as JPEG
        $result = imagejpeg($resized, $outputPath, 90);

        imagedestroy($image);
        imagedestroy($resized);

        return $result;
    }

    /**
     * Load image from file
     */
    private function loadImage(string $path): ?\GdImage
    {
        $info = getimagesize($path);
        if (!$info) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    /**
     * Preserve transparency for PNG/GIF
     */
    private function preserveTransparency(\GdImage $image, string $sourcePath): void
    {
        $info = getimagesize($sourcePath);

        if ($info[2] === IMAGETYPE_PNG || $info[2] === IMAGETYPE_GIF) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
        } else {
            // Fill with white for JPEG
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $white);
        }
    }

    /**
     * Extract dominant color from image
     */
    public function extractDominantColor(string $path): ?string
    {
        $image = $this->loadImage($path);
        if (!$image) {
            return null;
        }

        // Resize to 1x1 pixel to get average color
        $pixel = imagecreatetruecolor(1, 1);
        imagecopyresampled($pixel, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));

        $rgb = imagecolorat($pixel, 0, 0);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($image);
        imagedestroy($pixel);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Extract color palette from image (top 5 colors)
     */
    public function extractColorPalette(string $path, int $numColors = 5): array
    {
        $image = $this->loadImage($path);
        if (!$image) {
            return [];
        }

        // Resize to small image for faster processing
        $smallWidth = 50;
        $smallHeight = 50;
        $small = imagecreatetruecolor($smallWidth, $smallHeight);
        imagecopyresampled($small, $image, 0, 0, 0, 0, $smallWidth, $smallHeight, imagesx($image), imagesy($image));
        imagedestroy($image);

        // Count colors
        $colors = [];
        for ($x = 0; $x < $smallWidth; $x++) {
            for ($y = 0; $y < $smallHeight; $y++) {
                $rgb = imagecolorat($small, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Quantize colors to reduce similar shades
                $r = (int) (round($r / 32) * 32);
                $g = (int) (round($g / 32) * 32);
                $b = (int) (round($b / 32) * 32);

                $hex = sprintf('#%02x%02x%02x', $r, $g, $b);
                $colors[$hex] = ($colors[$hex] ?? 0) + 1;
            }
        }

        imagedestroy($small);

        // Sort by frequency and return top colors
        arsort($colors);
        return array_slice(array_keys($colors), 0, $numColors);
    }

    /**
     * Get image dimensions
     */
    public function getDimensions(string $path): ?array
    {
        $info = getimagesize($path);
        if (!$info) {
            return null;
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'mime' => $info['mime'],
        ];
    }

    /**
     * Delete all versions of an image
     */
    public function deleteAll(string $originalPath, ?string $thumbnailPath = null, ?string $webpPath = null): void
    {
        $paths = array_filter([$originalPath, $thumbnailPath, $webpPath]);

        foreach ($paths as $path) {
            $absolutePath = $this->uploadPath . '/' . $path;
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }
}
