<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FileStorageService
 *
 * Reusable service for file uploads, saves, and management.
 * Handles uploads for: avatars, photos, documents, PDFs, exports, etc.
 *
 * Usage:
 * $service = app(FileStorageService::class);
 * $path = $service->uploadFile($request->file('photo'), 'students/photos');
 * $service->deleteFile($oldPath);
 */
class FileStorageService
{
    /**
     * Default disk to use.
     */
    protected string $defaultDisk = 'public';

    /**
     * Upload a single file to storage.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $directory  Subdirectory inside the disk (e.g., "students/photos")
     * @param  string|null  $filename  Custom filename (without extension), or null for auto-generated
     * @param  string|null  $disk  Storage disk name, or null for default
     * @return string The relative path of the stored file
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        ?string $disk = null
    ): string {
        $disk = $disk ?? $this->defaultDisk;
        $directory = trim($directory, '/');

        $extension = $file->getClientOriginalExtension();
        $name = $filename ? Str::slug($filename) : Str::uuid()->toString();
        $fullName = "{$name}.{$extension}";

        // Store file
        $path = $file->storeAs($directory, $fullName, $disk);

        return $path;
    }

    /**
     * Upload an image with automatic resizing/optimization.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory
     * @param  array  $options  ['width' => 800, 'height' => 800, 'quality' => 80]
     * @return string Stored path
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory,
        array $options = []
    ): string {
        // Default options
        $options = array_merge([
            'width' => 1200,
            'height' => null, // Maintain aspect ratio
            'quality' => 85,
            'format' => null, // Keep original
        ], $options);

        $directory = trim($directory, '/');
        $extension = $options['format'] ?? $file->getClientOriginalExtension();
        $filename = Str::uuid()->toString() . '.' . $extension;
        $fullPath = "{$directory}/{$filename}";

        // Use Intervention Image to optimize
        if (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) {
            $image = \Intervention\Image\Laravel\Facades\Image::read($file->getRealPath());

            // Resize if width specified
            if ($options['width'] || $options['height']) {
                $image->scaleDown($options['width'], $options['height']);
            }

            // Encode based on format
            $encoded = match ($extension) {
                'jpg', 'jpeg' => $image->toJpeg($options['quality']),
                'png' => $image->toPng(),
                'webp' => $image->toWebp($options['quality']),
                default => $image->toJpeg($options['quality']),
            };

            Storage::disk($this->defaultDisk)->put($fullPath, (string) $encoded);

            return $fullPath;
        }

        // Fallback: regular upload without resize
        return $this->uploadFile($file, $directory);
    }

    /**
     * Save raw content to a file (e.g., generated PDF, CSV content).
     *
     * @param  string  $content  Raw file content
     * @param  string  $directory  Target directory
     * @param  string  $filename  Filename with extension
     * @param  string|null  $disk
     * @return string Stored path
     */
    public function saveContent(
        string $content,
        string $directory,
        string $filename,
        ?string $disk = null
    ): string {
        $disk = $disk ?? $this->defaultDisk;
        $directory = trim($directory, '/');
        $path = "{$directory}/{$filename}";

        Storage::disk($disk)->put($path, $content);

        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param  string  $path  Relative path
     * @param  string|null  $disk
     * @return bool
     */
    public function deleteFile(?string $path, ?string $disk = null): bool
    {
        if (empty($path)) {
            return false;
        }

        $disk = $disk ?? $this->defaultDisk;

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete multiple files.
     *
     * @param  array  $paths
     * @param  string|null  $disk
     * @return int Number of files deleted
     */
    public function deleteFiles(array $paths, ?string $disk = null): int
    {
        $deleted = 0;
        foreach ($paths as $path) {
            if ($this->deleteFile($path, $disk)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    /**
     * Get full URL for a stored file.
     *
     * @param  string|null  $path
     * @param  string|null  $disk
     * @return string|null
     */
    public function getUrl(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->url($path);
    }

    /**
     * Check if file exists.
     */
    public function exists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get file size in bytes.
     */
    public function size(string $path, ?string $disk = null): int
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->size($path);
    }

    /**
     * Move file to a new location.
     */
    public function move(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Copy file to a new location.
     */
    public function copy(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Generate a tenant-specific path.
     * Example: "tenants/{tenant_id}/students/photos"
     */
    public function tenantPath(string $directory): string
    {
        $tenantId = tenant() ? tenant()->getTenantKey() : 'central';
        return "tenants/{$tenantId}/" . trim($directory, '/');
    }
}
