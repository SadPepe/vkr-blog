<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Symfony\Component\Process\Process;
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

        $webpPath = $directory . '/' . $baseName . '.webp';

        try {
            $manager = self::manager();

            if ($manager !== null) {
                $image = $manager->read($source);
                $image->orient();
                $image->scaleDown(width: $maxWidth, height: $maxHeight);

                Storage::disk('public')->put($webpPath, (string) $image->toWebp($quality));

                return $webpPath;
            }
        } catch (Throwable) {
            // Нет поддержки WebP или файл не читается — пробуем ImageMagick ниже.
        }

        // На хосте без GD/Imagick конвертируем через CLI, чтобы не отдавать исходный JPEG
        if (self::convertWithCli($source, $webpPath, $maxWidth, $maxHeight, $quality)) {
            return $webpPath;
        }

        $path = $directory . '/' . $baseName . '.' . $extension;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /**
     * Запасной конвертер для окружений без GD/Imagick: ImageMagick CLI.
     * В Docker с GD сюда не доходит; нужен локальному PHP и хосту без расширений.
     */
    private static function convertWithCli(string $source, string $path, int $maxWidth, int $maxHeight, int $quality): bool
    {
        $binary = self::cliBinary();

        if ($binary === null) {
            return false;
        }

        $temp = tempnam(sys_get_temp_dir(), 'image-webp-');

        if ($temp === false) {
            return false;
        }

        try {
            $process = new Process([
                $binary,
                $source,
                '-auto-orient',
                '-resize',
                $maxWidth . 'x' . $maxHeight . '>',
                '-quality',
                (string) $quality,
                'webp:' . $temp,
            ]);
            $process->setTimeout(30);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($temp) || filesize($temp) === 0) {
                return false;
            }

            Storage::disk('public')->put($path, (string) file_get_contents($temp));

            return true;
        } catch (Throwable) {
            return false;
        } finally {
            if (is_file($temp)) {
                @unlink($temp);
            }
        }
    }

    private static function cliBinary(): ?string
    {
        if (! function_exists('proc_open')) {
            return null;
        }

        foreach (['/usr/bin/magick', '/usr/bin/convert', '/usr/local/bin/magick', '/usr/local/bin/convert'] as $binary) {
            if (is_executable($binary)) {
                return $binary;
            }
        }

        return null;
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
