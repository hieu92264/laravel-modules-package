<?php

declare(strict_types=1);

namespace HieuDev92264\LaravelModules\Tests;

use Buglinjo\LaravelWebp\WebpServiceProvider;
use HieuDev92264\LaravelModules\LaravelModuleServiceProvider;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected string $modulesPath;

    protected string $uploadsPath;

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModuleServiceProvider::class,
            WebpServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $suffix = str_replace('.', '', uniqid('laravel-modules-', true));
        $this->modulesPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$suffix.DIRECTORY_SEPARATOR.'Modules';
        $this->uploadsPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$suffix.DIRECTORY_SEPARATOR.'Uploads';

        $app['config']->set('app.name', 'Package Test API');
        $app['config']->set('app.url', 'https://package.test');
        $app['config']->set('modules.base_path', $this->modulesPath);
        $app['config']->set('modules.namespace', 'App\\Modules');
        $app['config']->set('modules.api_prefix', 'v1');
        $app['config']->set('modules.stubs_path', $this->modulesPath.DIRECTORY_SEPARATOR.'stubs');
        $app['config']->set('modules.postman', [
            'auth_middleware' => ['auth', 'auth:*'],
            'auth_type' => 'bearer',
            'token_variable' => 'api_token',
            'base_url_variable' => 'api_url',
        ]);
        $app['config']->set('laravel-webp', require __DIR__.'/../vendor/buglinjo/laravel-webp/config/laravel-webp.php');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->modulesPath));

        parent::tearDown();
    }
}
