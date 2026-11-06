<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Сохраняет картинки в публичный диск в WebP, уменьшая их до заданных
 * размеров без обрезки. Если GD/Imagick недоступны (например, при запуске
 * тестов на хосте), сохраняет исходный файл как есть.
 */
class ImageStore
{
    public const DEFAULT_MAX_WIDTH = 1600;
    public const DEFAULT_MAX_HEIGHT = 1600;
    public const DEFAULT_QUALITY = 82;

    /**
     * Загруженный файл → WebP в указанной папке публичного диска.
     *
     * @return string относительный путь внутри диска public
     */
    public static function storeUploaded(
        UploadedFile $file,
        string $directory,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT,
        int $quality = self::DEFAULT_QUALITY,
    ): string {
        return self::storePath(
            $file->getRealPath(),
            $directory,
            self::randomBaseName(),
            $maxWidth,
            $maxHeight,
            $quality,
            $file->getClientOriginalExtension(),
        );
    }

    /**
     * Файл на диске → WebP в указанной папке публичного диска.
     *
     * @return string относительный путь внутри диска public
     */
    public static function storePath(
        string $source,
        string $directory,
        ?string $baseName = null,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $maxHeight = self::DEFAULT_MAX_HEIGHT,
        int $quality = self::DEFAULT_QUALITY,
        ?string $fallbackExtension = null,
    ): string {
        $directory = trim($directory, '/');
        $baseName = $baseName ?: self::randomBaseName();
        $extension = strtolower((string) ($fallbackExtension ?: pathinfo($source, PATHINFO_EXTENSION)));

        if ($extension === '') {
            $extension = 'jpg';
        }

        try {
            $manager = self::manager();

            if ($manager !== null) {
                $image = $manager->read($source);
                $image->orient();
                $image->scaleDown(width: $maxWidth, height: $maxHeight);

                $path = $directory . '/' . $baseName . '.webp';
                Storage::disk('public')->put($path, (string) $image->toWebp($quality));

                return $path;
            }
        } catch (Throwable) {
            // Нет поддержки WebP или файл не читается — ниже сохраним оригинал.
        }

        $path = $directory . '/' . $baseName . '.' . $extension;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    private static function randomBaseName(): string
    {
        return Str::lower(Str::random(24));
    }

    private static function manager(): ?ImageManager
    {
        if (extension_loaded('gd')) {
            return ImageManager::gd();
        }

        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }

        return null;
    }
}
