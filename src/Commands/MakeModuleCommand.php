<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Commands\Concerns\InteractsWithModules;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    use InteractsWithModules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name : Module name}';

    /**
     * The console command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['module:make'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module directory structure';

    /**
     * Execute the console command.
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $module = $this->normalizeModuleName((string) $this->argument('name'));

        if (! $this->ensureValidName($module, 'module') || ! $this->ensureModuleDoesNotExist($module)) {
            return self::FAILURE;
        }

        $modulePath = $this->modulePath($module);
        $directories = [
            'Controllers',
            'Database'.DIRECTORY_SEPARATOR.'Migrations',
            'Database'.DIRECTORY_SEPARATOR.'Seeds',
            'DTOs',
            'Interfaces',
            'Middlewares',
            'Models',
            'Repositories',
            'Requests',
            'Routes',
            'Services',
        ];

        $this->ensureDirectory($modulePath);

        foreach ($directories as $directory) {
            $fullPath = $modulePath.DIRECTORY_SEPARATOR.$directory;

            $this->ensureDirectory($fullPath);

            if ($directory !== 'Routes') {
                $this->touchGitkeep($fullPath);
            }
        }

        $this->putStub(
            'routes/index.stub',
            $modulePath.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'index.php',
            ['{{ moduleKebab }}' => Str::kebab($module)]
        );

        $this->components->info("Module [{$module}] created successfully.");

        return self::SUCCESS;
    }
}
