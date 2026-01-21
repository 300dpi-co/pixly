<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Upload Service
 *
 * Handles file uploads with validation and storage.
 */
class UploadService
{
    private string $uploadPath;
    private array $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    private int $maxFileSize = 10485760; // 10MB
    private array $errors = [];

    public function __construct(?string $uploadPath = null)
    {
        $this->uploadPath = $uploadPath ?? \ROOT_PATH . '/public_html/uploads';
    }

    /**
     * Upload a single file
     */
    public function upload(array $file, string $subdir = 'images'): ?array
    {
        $this->errors = [];

        // Validate the upload
        if (!$this->validateUpload($file)) {
            return null;
        }

        // Create directory structure
        $dateDir = date('Y/m');
        $fullPath = $this->uploadPath . '/' . $subdir . '/' . $dateDir;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Generate unique filename
        $extension = $this->allowedMimes[$file['type']];
        $filename = $this->generateFilename($extension);
        $relativePath = $subdir . '/' . $dateDir . '/' . $filename;
        $absolutePath = $this->uploadPath . '/' . $relativePath;

        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            $this->errors[] = 'Failed to move uploaded file.';
            return null;
        }

        // Get image dimensions
        $imageInfo = getimagesize($absolutePath);

        return [
            'filename' => $filename,
            'original_filename' => $file['name'],
            'relative_path' => $relativePath,
            'absolute_path' => $absolutePath,
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
        ];
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, string $subdir = 'images'): array
    {
        $results = [];

        // Normalize the files array
        $normalized = $this->normalizeFilesArray($files);

        foreach ($normalized as $file) {
            $result = $this->upload($file, $subdir);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Validate an uploaded file
     */
    private function validateUpload(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = 'File size exceeds maximum allowed (' . ($this->maxFileSize / 1048576) . 'MB).';
            return false;
        }

        // Check MIME type
        if (!isset($this->allowedMimes[$file['type']])) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', array_keys($this->allowedMimes));
            return false;
        }

        // Verify it's actually an image using magic bytes
        if (!$this->verifyImageMagicBytes($file['tmp_name'], $file['type'])) {
            $this->errors[] = 'File does not appear to be a valid image.';
            return false;
        }

        return true;
    }

    /**
     * Verify image using magic bytes (file signature)
     */
    private function verifyImageMagicBytes(string $filepath, string $declaredMime): bool
    {
        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }

        $bytes = fread($handle, 12);
        fclose($handle);

        $signatures = [
            'image/jpeg' => ["\xFF\xD8\xFF"],
            'image/png' => ["\x89PNG\r\n\x1a\n"],
            'image/gif' => ["GIF87a", "GIF89a"],
            'image/webp' => ["RIFF"],
        ];

        if (!isset($signatures[$declaredMime])) {
            return false;
        }

        foreach ($signatures[$declaredMime] as $signature) {
            if (str_starts_with($bytes, $signature)) {
                // Additional check for WebP
                if ($declaredMime === 'image/webp') {
                    return str_contains($bytes, 'WEBP');
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * Normalize files array from multiple file upload
     */
    private function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        // Check if it's a standard single file or array format
        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files format: files[name][0], files[name][1], etc.
            foreach ($files['name'] as $index => $name) {
                if ($files['error'][$index] === UPLOAD_ERR_OK) {
                    $normalized[] = [
                        'name' => $name,
                        'type' => $files['type'][$index],
                        'tmp_name' => $files['tmp_name'][$index],
                        'error' => $files['error'][$index],
                        'size' => $files['size'][$index],
                    ];
                }
            }
        } elseif (isset($files['name'])) {
            // Single file format
            $normalized[] = $files;
        }

        return $normalized;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
            default => 'Unknown upload error.',
        };
    }

    /**
     * Delete a file
     */
    public function delete(string $relativePath): bool
    {
        $absolutePath = $this->uploadPath . '/' . $relativePath;

        if (file_exists($absolutePath)) {
            return unlink($absolutePath);
        }

        return false;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set allowed MIME types
     */
    public function setAllowedMimes(array $mimes): self
    {
        $this->allowedMimes = $mimes;
        return $this;
    }

    /**
     * Set max file size
     */
    public function setMaxFileSize(int $bytes): self
    {
        $this->maxFileSize = $bytes;
        return $this;
    }
}
