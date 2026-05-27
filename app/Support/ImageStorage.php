<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageStorage
{
    public const PLACEHOLDER = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%201400%20900%22%20role%3D%22img%22%20aria-label%3D%22Image%20placeholder%22%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22g%22%20x1%3D%220%22%20x2%3D%221%22%20y1%3D%220%22%20y2%3D%221%22%3E%3Cstop%20offset%3D%220%22%20stop-color%3D%22%23f5f5f4%22%2F%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%23e7e5e4%22%2F%3E%3C%2FlinearGradient%3E%3C%2Fdefs%3E%3Crect%20width%3D%221400%22%20height%3D%22900%22%20fill%3D%22url(%23g)%22%2F%3E%3Crect%20x%3D%22180%22%20y%3D%22210%22%20width%3D%221040%22%20height%3D%22480%22%20rx%3D%2232%22%20fill%3D%22%23ffffff%22%20opacity%3D%22.65%22%2F%3E%3Ccircle%20cx%3D%22435%22%20cy%3D%22355%22%20r%3D%2255%22%20fill%3D%22%23d6d3d1%22%2F%3E%3Cpath%20d%3D%22M260%20610l250-230%20210%20180%20135-115%20285%20265H260z%22%20fill%3D%22%23a8a29e%22%20opacity%3D%22.72%22%2F%3E%3Ctext%20x%3D%22700%22%20y%3D%22770%22%20text-anchor%3D%22middle%22%20fill%3D%22%2378716c%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2244%22%20font-weight%3D%22700%22%3EVilla%20Estella%3C%2Ftext%3E%3C%2Fsvg%3E';

    public static function disk(): string
    {
        return config('filesystems.uploads_disk', 'public');
    }

    public static function store(UploadedFile $image, string $directory): string
    {
        $disk = self::disk();

        Storage::disk($disk)->makeDirectory($directory);

        $path = $image->storePublicly($directory, $disk);

        if (! is_string($path) || ! Storage::disk($disk)->exists($path)) {
            Log::error('Image upload failed after storage write', [
                'disk' => $disk,
                'directory' => $directory,
                'path' => $path,
            ]);

            abort(500, 'The image could not be saved. Please check the upload storage configuration.');
        }

        return $path;
    }

    public static function url(?string $path, ?string $fallback = null): string
    {
        $path = trim((string) $path);
        $fallback ??= self::PLACEHOLDER;

        if ($path === '') {
            return $fallback;
        }

        if (self::isExternal($path)) {
            return $path;
        }

        $normalized = self::normalizePath($path);
        $disk = self::disk();

        if ($disk === 'public' && ! Storage::disk('public')->exists($normalized)) {
            return $fallback;
        }

        try {
            $url = Storage::disk($disk)->url($normalized);

            return filled($url) ? $url : $fallback;
        } catch (Throwable $exception) {
            Log::warning('Could not generate image URL from upload disk', [
                'disk' => $disk,
                'path' => $normalized,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            if (Storage::disk('public')->exists($normalized)) {
                return Storage::disk('public')->url($normalized);
            }

            return $fallback;
        }
    }

    public static function exists(?string $path, ?string $disk = null): bool
    {
        $path = trim((string) $path);

        if ($path === '' || self::isExternal($path)) {
            return false;
        }

        return Storage::disk($disk ?? self::disk())->exists(self::normalizePath($path));
    }

    public static function delete(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || self::isExternal($path)) {
            return;
        }

        $normalized = self::normalizePath($path);

        foreach (array_unique([self::disk(), 'public']) as $disk) {
            try {
                if (Storage::disk($disk)->exists($normalized)) {
                    Storage::disk($disk)->delete($normalized);
                }
            } catch (Throwable $exception) {
                Log::warning('Could not delete stored image', [
                    'disk' => $disk,
                    'path' => $normalized,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    public static function isExternal(?string $path): bool
    {
        $path = trim((string) $path);

        return str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, 'data:');
    }

    public static function normalizePath(?string $path): string
    {
        $path = ltrim(trim((string) $path), '/');

        return str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/'))
            : $path;
    }
}
