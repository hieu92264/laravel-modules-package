<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Commands\Concerns\InteractsWithModules;
use Illuminate\Console\Command;

class MakeModuleModelCommand extends Command
{
    use InteractsWithModules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:model
        {name : Model name}
        {module : Module name}
        {--force : Overwrite the model if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model inside an existing module';


    /**
     * Execute the console command.
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $module = $this->normalizeModuleName((string) $this->argument('module'));
        $model = $this->normalizeClassName((string) $this->argument('name'));

        // Đã sửa $service thành $model
        if (! $this->ensureValidName($module, 'module') || ! $this->ensureValidName($model, 'model')) {
            return self::FAILURE;
        }

        $modulePath = $this->ensureModuleExists($module);

        if ($modulePath === null) {
            return self::FAILURE;
        }

        $modelPath = $modulePath . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $model . '.php';

        if (! $this->ensureFilesCanBeCreated([$modelPath], (bool) $this->option('force'))) {
            return self::FAILURE;
        }

        $this->putStub('model.stub', $modelPath, [
            '{{ namespace }}' => $this->moduleNamespace($module, 'Models'),
            '{{ class }}' => $model,
        ]);

        // Đã sửa chữ "Service" thành "Model"
        $this->components->info("Model [{$model}] created successfully in module [{$module}].");

        return self::SUCCESS;
    }
}
