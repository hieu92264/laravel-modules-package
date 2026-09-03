<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Commands\Concerns\InteractsWithModules;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeModuleDtoCommand extends Command
{
    use InteractsWithModules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:dto
        {name : DTO class name}
        {module : Module name}
        {--force : Overwrite the DTO if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a DTO inside an existing module';

    /**
     * Execute the console command.
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $module = $this->normalizeModuleName((string) $this->argument('module'));
        $dto = $this->normalizeClassName((string) $this->argument('name'));

        if (! $this->ensureValidName($module, 'module') || ! $this->ensureValidName($dto, 'dto')) {
            return self::FAILURE;
        }

        $modulePath = $this->ensureModuleExists($module);

        if ($modulePath === null) {
            return self::FAILURE;
        }

        $destinationPath = $modulePath.DIRECTORY_SEPARATOR.'DTOs'.DIRECTORY_SEPARATOR.$dto.'.php';

        if (! $this->ensureFilesCanBeCreated([$destinationPath], (bool) $this->option('force'))) {
            return self::FAILURE;
        }

        $this->putStub('dto.stub', $destinationPath, [
            '{{ namespace }}' => $this->moduleNamespace($module, 'DTOs'),
            '{{ class }}' => $dto,
        ]);

        $this->components->info("DTO [{$dto}] created successfully in module [{$module}].");

        return self::SUCCESS;
    }
}
