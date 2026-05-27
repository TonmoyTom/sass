<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;

/**
 * HasFiles Trait
 *
 * Reusable trait for models that have file uploads (avatar, photo, document, etc.).
 * Automatically handles file replacement and deletion.
 *
 * Usage in Model:
 *
 * class Student extends Model
 * {
 *     use HasFiles;
 *
 *     // Define file attributes (column => storage_path)
 *     protected array $fileAttributes = [
 *         'photo' => 'students/photos',
 *         'birth_certificate' => 'students/documents',
 *     ];
 * }
 *
 * Usage in Controller:
 *
 * if ($request->hasFile('photo')) {
 *     $student->setFile('photo', $request->file('photo'));
 * }
 * $student->save();
 */
trait HasFiles
{
    /**
     * Boot the trait.
     */
    public static function bootHasFiles(): void
    {
        // Delete files when model is deleted
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return; // Soft delete - keep files
            }
            $model->deleteAllFiles();
        });
    }

    /**
     * Set a file attribute (uploads + deletes old file).
     */
    public function setFile(string $attribute, UploadedFile $file, array $options = []): self
    {
        if (!$this->isFileAttribute($attribute)) {
            throw new \Exception("'{$attribute}' is not a defined file attribute on this model");
        }

        $service = app(FileStorageService::class);

        // Delete old file if exists
        if ($this->{$attribute}) {
            $service->deleteFile($this->{$attribute});
        }

        // Get directory for this attribute
        $directory = $this->fileAttributes[$attribute];

        // For images, use uploadImage with optimization
        $isImage = in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif']);

        $path = $isImage
            ? $service->uploadImage($file, $directory, $options)
            : $service->uploadFile($file, $directory);

        $this->{$attribute} = $path;

        return $this;
    }

    /**
     * Remove a file attribute (deletes the file).
     */
    public function removeFile(string $attribute): self
    {
        if (!$this->isFileAttribute($attribute)) {
            return $this;
        }

        if ($this->{$attribute}) {
            app(FileStorageService::class)->deleteFile($this->{$attribute});
            $this->{$attribute} = null;
        }

        return $this;
    }

    /**
     * Get the URL for a file attribute.
     */
    public function getFileUrl(string $attribute): ?string
    {
        if (!$this->{$attribute}) {
            return null;
        }

        return app(FileStorageService::class)->getUrl($this->{$attribute});
    }

    /**
     * Delete all files associated with this model.
     */
    public function deleteAllFiles(): void
    {
        if (!isset($this->fileAttributes) || !is_array($this->fileAttributes)) {
            return;
        }

        $service = app(FileStorageService::class);
        $paths = [];

        foreach (array_keys($this->fileAttributes) as $attribute) {
            if ($this->{$attribute}) {
                $paths[] = $this->{$attribute};
            }
        }

        if (!empty($paths)) {
            $service->deleteFiles($paths);
        }
    }

    /**
     * Check if attribute is defined as file attribute.
     */
    protected function isFileAttribute(string $attribute): bool
    {
        return isset($this->fileAttributes) && array_key_exists($attribute, $this->fileAttributes);
    }
}
