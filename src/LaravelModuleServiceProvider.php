<?php

namespace HieuDev92264\LaravelModules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;

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
                Commands\MakeModuleModelCommand::class
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
        $this->registerPrefixRoutes();
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

    private function registerPrefixRoutes(): void
    {
        $apiPrefix = trim((string) config('modules.api_prefix', 'api'), '/');
        $basePath = config('modules.base_path', app_path('Modules'));

        $moduleRoutes = glob($basePath . '/*/Routes/*.php') ?: [];

        foreach ($moduleRoutes as $routeFile) {
            $fileName = basename($routeFile);

            if ($fileName === 'web.php') {
                Route::middleware('web')->group($routeFile);
            } else {
                Route::middleware('api')->prefix($apiPrefix)->group($routeFile);
            }
        }
    }
}
