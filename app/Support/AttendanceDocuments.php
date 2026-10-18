<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Storage for attendance supporting documents and appeal (excuse letter)
 * documents.
 *
 * New files go to the private "local" disk (storage/app/private), which is not
 * symlinked into public/. Files uploaded before this change live on the
 * "public" disk; they are still found there so existing records keep working,
 * but are only ever served through AttendanceDocumentController.
 */
class AttendanceDocuments
{
    public const DISK = 'local';

    public const LEGACY_DISK = 'public';

    private const FOLDERS = ['appeal_documents/', 'attendance_documents/'];

    public static function store(UploadedFile $file, string $folder, string $filename): string|false
    {
        return $file->storeAs($folder, $filename, self::DISK);
    }

    /**
     * The disk that currently holds $path, or null when the path is not a
     * document path or the file is missing.
     */
    public static function diskFor(?string $path): ?string
    {
        if (! self::isDocumentPath($path)) {
            return null;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    public static function delete(?string $path): void
    {
        if (! self::isDocumentPath($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
        Storage::disk(self::LEGACY_DISK)->delete($path);
    }

    private static function isDocumentPath(?string $path): bool
    {
        if (! filled($path) || str_contains($path, '..') || str_contains($path, '\\')) {
            return false;
        }

        foreach (self::FOLDERS as $folder) {
            if (str_starts_with($path, $folder)) {
                return true;
            }
        }

        return false;
    }
}
