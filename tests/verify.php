<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/** @param bool $condition */
function assertPackage(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$expectedClasses = [
    'HieuDev92264\\LaravelModules\\Traits\\ApiResponse' => 'src/Traits/ApiResponse.php',
    'HieuDev92264\\LaravelModules\\Traits\\HasBaseMetadata' => 'src/Traits/HasBaseMetadata.php',
    'HieuDev92264\\LaravelModules\\Base\\BaseModel' => 'src/Base/BaseModel.php',
    'HieuDev92264\\LaravelModules\\LaravelModuleServiceProvider' => 'src/LaravelModuleServiceProvider.php',
];

foreach ($expectedClasses as $class => $relativePath) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    assertPackage(is_file($path), "{$class} must be stored at {$relativePath}.");

    $contents = file_get_contents($path);
    assertPackage($contents !== false && str_contains($contents, "namespace ".substr($class, 0, strrpos($class, '\\')).';'), "{$relativePath} has an unexpected namespace.");
}

$modelStub = file_get_contents($root.'/src/stubs/model.stub');
assertPackage($modelStub !== false && str_contains($modelStub, 'HieuDev92264\\LaravelModules\\Base\\BaseModel'), 'The model stub must import Base\\BaseModel.');
assertPackage($modelStub !== false && ! str_contains($modelStub, 'Bases\\BaseModel'), 'The obsolete Bases\\BaseModel namespace must not be generated.');

$moduleInteraction = file_get_contents($root.'/src/Commands/Concerns/InteractsWithModules.php');
assertPackage($moduleInteraction !== false && str_contains($moduleInteraction, "config('modules.stubs_path'"), 'Published stubs must be selectable through modules.stubs_path.');
assertPackage($moduleInteraction !== false && str_contains($moduleInteraction, 'File::isFile($publishedStubPath)'), 'A missing published stub must fall back to the package stub.');

$apiResponse = file_get_contents($root.'/src/Traits/ApiResponse.php');
assertPackage($apiResponse !== false && str_contains($apiResponse, "'status_code' => \$statusCode"), 'ApiResponse must retain the status_code contract.');
assertPackage($apiResponse !== false && ! str_contains($apiResponse, "'trace' => array_slice(\$exception->getTrace()"), 'Exception trace arguments must not be returned directly.');

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true);
assertPackage(is_array($composer), 'composer.json must be valid JSON.');
assertPackage(isset($composer['require']['php']), 'composer.json must declare a PHP requirement.');
assertPackage(! isset($composer['minimum-stability']), 'A library must not force consumers onto dev stability.');

fwrite(STDOUT, "Package static checks passed.\n");
