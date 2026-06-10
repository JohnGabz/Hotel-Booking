<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ImageInput
{
    public static function resolve(Request $request, string $fileField = 'image', string $urlField = 'image_url', string $modeField = 'image_input_mode', string $directory = 'uploads', ?string $existing = null): ?string
    {
        $mode = $request->input($modeField, 'upload');

        if ($mode === 'url') {
            $url = trim((string) $request->input($urlField, ''));

            return $url !== '' ? $url : $existing;
        }

        if ($request->hasFile($fileField) && $request->file($fileField) instanceof UploadedFile) {
            return ImageStorage::store($request->file($fileField), $directory);
        }

        return $existing;
    }

    /**
     * @return array<int, string>
     */
    public static function resolveMany(Request $request, string $filesField = 'images', string $linksField = 'image_links', string $urlField = 'image_url', string $modeField = 'image_input_mode', string $directory = 'rooms'): array
    {
        $mode = $request->input($modeField, 'upload');
        $paths = [];

        if ($mode === 'url') {
            $singleUrl = trim((string) $request->input($urlField, ''));
            if ($singleUrl !== '' && self::isValidUrl($singleUrl)) {
                $paths[] = $singleUrl;
            }

            $paths = array_merge($paths, self::parseLinks((string) $request->input($linksField, '')));
        } elseif ($request->hasFile($filesField)) {
            foreach ((array) $request->file($filesField, []) as $file) {
                if ($file instanceof UploadedFile) {
                    $paths[] = ImageStorage::store($file, $directory);
                }
            }
        }

        return array_values(array_unique($paths));
    }

    public static function deleteLocal(?string $path): void
    {
        ImageStorage::delete($path);
    }

    /**
     * @return array<int, string>
     */
    protected static function parseLinks(string $links): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $links) ?: [])
            ->map(fn ($url) => trim((string) $url))
            ->filter(fn ($url) => self::isValidUrl($url))
            ->unique()
            ->values()
            ->all();
    }

    protected static function isValidUrl(string $url): bool
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
