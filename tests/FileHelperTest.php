<?php

declare(strict_types=1);

namespace HieuDev92264\LaravelModules\Tests;

use HieuDev92264\LaravelModules\Common\FileHelper;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class FileHelperTest extends TestCase
{
    public function test_it_saves_an_uploaded_file_and_returns_metadata(): void
    {
        $file = UploadedFile::fake()->createWithContent('Invoice Copy.txt', 'package upload');

        $result = FileHelper::save($file, $this->uploadsPath);

        $this->assertFileExists($result['path']);
        $this->assertSame('Invoice Copy.txt', $result['original_name']);
        $this->assertSame('txt', $result['extension']);
        $this->assertSame($result['filename'], basename($result['path']));
        $this->assertSame(filesize($result['path']), $result['size']);
        $this->assertNotSame('', $result['mime_type']);
    }

    public function test_it_converts_an_image_to_webp_and_returns_metadata(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('The GD WebP extension is not available.');
        }

        $file = UploadedFile::fake()->image('avatar.png', 20, 20);

        $result = FileHelper::saveAsWebp($file, $this->uploadsPath);

        $this->assertFileExists($result['path']);
        $this->assertSame('webp', $result['extension']);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize($result['path'])[2]);
        $this->assertGreaterThan(0, $result['size']);
    }

    public function test_it_rejects_a_non_image_for_webp_conversion(): void
    {
        $file = UploadedFile::fake()->createWithContent('notes.txt', 'not an image');

        $this->expectException(InvalidArgumentException::class);

        FileHelper::saveAsWebp($file, $this->uploadsPath);
    }
}
