<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;

    public function __construct(string $disk = 'public')
    {
        $this->disk = $disk;
    }

    /**
     * Store infographic image.
     *
     * @param string $content Base64 or binary content
     * @param string $format Image format (png, jpg, etc.)
     * @return array{path: string, url: string}
     */
    public function storeInfographic(string $content, string $format = 'png'): array
    {
        $filename = $this->generateFilename('infographic', $format);
        $path = "infographics/{$filename}";

        // Decode base64 if needed
        if ($this->isBase64($content)) {
            $content = base64_decode($content);
        }

        Storage::disk($this->disk)->put($path, $content);

        return [
            'path' => $path,
            'url' => Storage::disk($this->disk)->url($path),
        ];
    }

    /**
     * Store AI-generated image.
     *
     * @param string $content Base64 or binary content
     * @param string $format Image format (png, jpg, etc.)
     * @return array{path: string, url: string}
     */
    public function storeImage(string $content, string $format = 'png'): array
    {
        $filename = $this->generateFilename('image', $format);
        $path = "images/{$filename}";

        // Decode base64 if needed
        if ($this->isBase64($content)) {
            $content = base64_decode($content);
        }

        Storage::disk($this->disk)->put($path, $content);

        return [
            'path' => $path,
            'url' => Storage::disk($this->disk)->url($path),
        ];
    }

    /**
     * Store thumbnail image.
     *
     * @param string $content Image content
     * @param string $format Image format
     * @return array{path: string, url: string}
     */
    public function storeThumbnail(string $content, string $format = 'png'): array
    {
        $filename = $this->generateFilename('thumbnail', $format);
        $path = "thumbnails/{$filename}";

        if ($this->isBase64($content)) {
            $content = base64_decode($content);
        }

        Storage::disk($this->disk)->put($path, $content);

        return [
            'path' => $path,
            'url' => Storage::disk($this->disk)->url($path),
        ];
    }

    /**
     * Delete file by path.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Check if file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get file URL.
     *
     * @param string $path
     * @return string
     */
    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Get file size.
     *
     * @param string $path
     * @return int
     */
    public function size(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Generate unique filename.
     *
     * @param string $prefix
     * @param string $extension
     * @return string
     */
    private function generateFilename(string $prefix, string $extension): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $random = Str::random(8);
        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Check if content is base64 encoded.
     *
     * @param string $content
     * @return bool
     */
    private function isBase64(string $content): bool
    {
        $decoded = base64_decode($content, true);

        if ($decoded === false) {
            return false;
        }

        return base64_encode($decoded) === $content;
    }
}
