<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Postman\PostmanCollectionBuilder;
use HieuDev92264\LaravelModules\Postman\RouteScanner;
use Illuminate\Console\Command;
use JsonException;

class GeneratePostmanCommand extends Command
{
    protected $signature = 'module:postman {--output=postman_api.json}';

    protected $description = 'Generate Postman collection for all modules';

    public function handle(RouteScanner $scanner, PostmanCollectionBuilder $builder): int
    {
        $this->info('Scanning module APIs...');

        $routes = $scanner->scan();

        if (empty($routes)) {
            $this->warn('No Module API routes found');

            return self::SUCCESS;
        }

        $collection = $builder->build($routes);

        $path = base_path((string) $this->option('output'));

        try {
            $json = json_encode(
                $collection,
                JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            $this->error("Could not encode Postman collection: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $this->error("Could not create output directory: {$directory}");

            return self::FAILURE;
        }

        if (file_put_contents($path, $json) === false) {
            $this->error("Could not write Postman collection: {$path}");

            return self::FAILURE;
        }

        $this->newLine();

        $this->info(
            sprintf(
                'Generated %d API routes.',
                count($routes)
            )
        );

        $this->info(
            "Postman collection: {$path}"
        );

        return self::SUCCESS;
    }
}
