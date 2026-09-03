<?php

namespace HieuDev92264\LaravelModules;

use Illuminate\Support\ServiceProvider;

class LaravelModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        // gộp cấu hình mặc định của package vào dự án gốc
        $this->mergeConfigFrom(__DIR__ . '/../config/modules.php', 'modules');
    }

    public function boot(): void
    {
        if($this->app->runningInConsole()) {
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
    }
}
