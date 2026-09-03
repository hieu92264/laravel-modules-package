<?php

namespace HieuDev92264\LaravelModules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class LaravelModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        // gộp cấu hình mặc định của package vào dự án gốc
        $this->mergeConfigFrom(__DIR__ . '/../config/modules.php', 'modules');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MakeModuleCommand::class,
                Commands\MakeModuleControllerCommand::class,
                Commands\MakeModuleDtoCommand::class,
                Commands\MakeModuleMigration::class,
                Commands\MakeModuleRepositoryCommand::class,
                Commands\MakeModuleServiceCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/modules.php' => config_path('modules.php')
            ], 'modules-config');

            $this->publishes([
                __DIR__ . '/stubs' => base_path('stubs/modules')
            ], 'modules-stubs');
        }

        $this->bootModuleMigrations();
        $this->registerBlueprintMacros();
    }

    private function bootModuleMigrations(): void
    {
        $basePath = config('modules.base_path', app_path('Modules'));

        $migrationPaths = glob($basePath . '/*/Database/Migrations');

        if (is_array($migrationPaths)) {
            foreach ($migrationPaths as $path) {
                $this->loadMigrationsFrom($path);
            }
        }
    }

    private function registerBlueprintMacros(): void
    {
        if (! Blueprint::hasMacro('metadataColumns')) {
            Blueprint::macro('metadataColumns', function () {
                /** @var Blueprint $this */
                $this->boolean('is_active')->default(true);
                $this->string('user_name_created')->nullable();
                $this->string('user_name_updated')->nullable();
            });
        }
    }
}
