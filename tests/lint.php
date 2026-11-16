<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [$root.'/config', $root.'/src', $root.'/tests'];

foreach ($paths as $path) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        passthru(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file->getPathname()), $exitCode);

        if ($exitCode !== 0) {
            exit($exitCode);
        }
    }
}
