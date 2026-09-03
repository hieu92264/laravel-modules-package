<?php

namespace HieuDev92264\LaravelModules\Commands;

use HieuDev92264\LaravelModules\Commands\Concerns\InteractsWithModules;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleMigration extends Command
{
    use InteractsWithModules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migration
        {name : The migration name}
        {module : The module name}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--force : Overwrite the migration file if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file for a specific module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->normalizeModuleName($this->argument('module'));
        $migrationName = $this->normalizeMigrationName((string) $this->argument('name'));

        if (! $this->ensureValidName($moduleName, 'module')) {
            return self::FAILURE;
        }

        if (! $this->ensureValidName($migrationName, 'migration')) {
            return self::FAILURE;
        }

        $modulePath = $this->ensureModuleExists($moduleName);

        if ($modulePath === null) {
            return self::FAILURE;
        }

        $stubRelativePath = $this->migrationStub($migrationName);
        $stubPath = $this->stubsBasePath().DIRECTORY_SEPARATOR.$stubRelativePath;

        if (! File::exists($stubPath)) {
            $this->error("Migration stub [{$stubPath}] does not exist.");

            return self::FAILURE;
        }

        $table = $this->migrationTable($migrationName);

        if ($table === '') {
            $this->error('Unable to determine the table name. Use --create=table_name or --table=table_name.');

            return self::FAILURE;
        }

        $migrationsPath = $modulePath.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations';
        $migrationPath = $migrationsPath.DIRECTORY_SEPARATOR.$this->migrationFileName($migrationName);

        if (! $this->ensureFilesCanBeCreated([$migrationPath], (bool) $this->option('force'))) {
            return self::FAILURE;
        }

        $this->putStub($stubRelativePath, $migrationPath, [
            '{{ table }}' => $table,
            '{{table}}' => $table,
        ]);

        $this->components->info("Migration [{$migrationPath}] created successfully.");

        return self::SUCCESS;
    }

    private function normalizeMigrationName(string $name): string
    {
        return Str::of($name)
            ->trim()
            ->replace(['\\', '/', '-'], '_')
            ->snake()
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }

    private function migrationFileName(string $name): string
    {
        return now()->format('Y_m_d_His').'_'.$name.'.php';
    }

    private function migrationTable(string $name): string
    {
        $table = $this->option('create') ?: $this->option('table');

        if (is_string($table) && trim($table) !== '') {
            return Str::snake(trim($table));
        }

        foreach ([
                     '/^create_(.+)_table$/',
                     '/^create_(.+)$/',
                     '/^.+_to_(.+)_table$/',
                     '/^.+_to_(.+)$/',
                     '/^.+_from_(.+)_table$/',
                     '/^.+_from_(.+)$/',
                     '/^.+_in_(.+)_table$/',
                     '/^.+_in_(.+)$/',
                     '/^.+_(.+)_table$/',
                 ] as $pattern) {
            if (preg_match($pattern, $name, $matches) === 1) {
                return Str::snake($matches[1]);
            }
        }

        return '';
    }

    private function migrationStub(string $name): string
    {
        if (is_string($this->option('create')) && trim((string) $this->option('create')) !== '') {
            return 'migration.create.stub';
        }

        if (is_string($this->option('table')) && trim((string) $this->option('table')) !== '') {
            return 'migration.update.stub';
        }

        if (preg_match('/^create_(.+)(_table)?$/', $name) === 1) {
            return 'migration.create.stub';
        }

        return 'migration.update.stub';
    }
}
