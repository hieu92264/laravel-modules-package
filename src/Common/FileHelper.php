<?php

declare(strict_types=1);

namespace HieuDev92264\LaravelModules\Common;

use Buglinjo\LaravelWebp\Webp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class FileHelper
{
    private function __construct()
    {
    }

    /**
     * @return array{path: string, filename: string, original_name: string, extension: string, mime_type: string, size: int}
     */
    public static function save(UploadedFile $file, string $directory): array
    {
        self::assertValidUpload($file);
        self::ensureDirectory($directory);

        $extension = strtolower($file->guessExtension() ?: 'bin');
        $filename = self::makeFilename($file, $extension);
        $path = self::joinPath($directory, $filename);

        try {
            $file->move($directory, $filename);
        } catch (Throwable $exception) {
            throw new RuntimeException("Unable to save uploaded file to '{$path}'.", 0, $exception);
        }

        return self::metadata($path, $filename, $file->getClientOriginalName(), $extension);
    }

    /**
     * @return array{path: string, filename: string, original_name: string, extension: string, mime_type: string, size: int}
     */
    public static function saveAsWebp(UploadedFile $file, string $directory): array
    {
        self::assertValidUpload($file);

        if (@getimagesize($file->getPathname()) === false) {
            throw new InvalidArgumentException('The uploaded file must be a valid image.');
        }

        self::ensureDirectory($directory);

        $filename = self::makeFilename($file, 'webp');
        $path = self::joinPath($directory, $filename);

        try {
            $saved = Webp::make($file)?->save($path);

            clearstatcache(true, $path);
            $image = is_file($path) ? @getimagesize($path) : false;

            if ($saved !== true || $image === false || $image[2] !== IMAGETYPE_WEBP) {
                throw new RuntimeException('The configured driver did not create a valid WebP image.');
            }

            return self::metadata($path, $filename, $file->getClientOriginalName(), 'webp');
        } catch (Throwable $exception) {
            clearstatcache(true, $path);

            if (is_file($path)) {
                @unlink($path);
            }

            throw new RuntimeException("Unable to convert uploaded image to WebP at '{$path}'.", 0, $exception);
        }
    }

    public static function exists(string $path): bool
    {
        clearstatcache(true, $path);

        return is_file($path);
    }

    public static function size(string $path): int
    {
        self::assertFileExists($path);

        $size = filesize($path);

        if ($size === false) {
            throw new RuntimeException("Unable to read size of '{$path}'.");
        }

        return $size;
    }

    public static function mimeType(string $path): string
    {
        self::assertFileExists($path);

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if ($mimeType === false || $mimeType === '') {
            throw new RuntimeException("Unable to detect MIME type of '{$path}'.");
        }

        return $mimeType;
    }

    private static function assertValidUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException("The uploaded file is invalid: {$file->getErrorMessage()}");
        }
    }

    private static function assertFileExists(string $path): void
    {
        if (! self::exists($path)) {
            throw new RuntimeException("File '{$path}' does not exist.");
        }
    }

    private static function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create directory '{$directory}'.");
        }
    }

    private static function makeFilename(UploadedFile $file, string $extension): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = rtrim(substr(Str::slug($name), 0, 100), '-');

        return sprintf('%s_%s_%s.%s', now()->format('Ymd'), $slug ?: 'file', Str::uuid(), strtolower($extension));
    }

    /**
     * @return array{path: string, filename: string, original_name: string, extension: string, mime_type: string, size: int}
     */
    private static function metadata(string $path, string $filename, string $originalName, string $extension): array
    {
        return [
            'path' => $path,
            'filename' => $filename,
            'original_name' => $originalName,
            'extension' => $extension,
            'mime_type' => self::mimeType($path),
            'size' => self::size($path),
        ];
    }

    private static function joinPath(string $directory, string $filename): string
    {
        return rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$filename;
    }
}
