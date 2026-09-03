<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Commands\Concerns\InteractsWithModules;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeModuleControllerCommand extends Command
{
    use InteractsWithModules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:controller
        {name : Controller name}
        {module : Module name}
        {--force : Overwrite the controller if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a controller inside an existing module';

    /**
     * Execute the console command.
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $module = $this->normalizeModuleName((string) $this->argument('module'));
        $controller = $this->normalizeClassName((string) $this->argument('name'), 'Controller');

        if (! $this->ensureValidName($module, 'module') || ! $this->ensureValidName($controller, 'controller')) {
            return self::FAILURE;
        }

        $modulePath = $this->ensureModuleExists($module);

        if ($modulePath === null) {
            return self::FAILURE;
        }

        $destinationPath = $modulePath.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$controller.'.php';

        if (! $this->ensureFilesCanBeCreated([$destinationPath], (bool) $this->option('force'))) {
            return self::FAILURE;
        }

        $this->putStub('controller.stub', $destinationPath, [
            '{{ namespace }}' => $this->moduleNamespace($module, 'Controllers'),
            '{{ class }}' => $controller,
        ]);

        $this->components->info("Controller [{$controller}] created successfully in module [{$module}].");

        return self::SUCCESS;
    }
}
