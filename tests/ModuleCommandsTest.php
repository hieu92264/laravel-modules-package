<?php

declare(strict_types=1);

namespace HieuDev92264\LaravelModules\Tests;

class ModuleCommandsTest extends TestCase
{
    public function test_it_creates_a_module_and_all_supported_artifacts(): void
    {
        $this->artisan('make:module', ['name' => 'Billing'])->assertSuccessful();

        foreach (['Controllers', 'Database/Migrations', 'DTOs', 'Interfaces', 'Models', 'Repositories', 'Routes', 'Services'] as $directory) {
            $this->assertDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'Billing'.DIRECTORY_SEPARATOR.$directory);
        }

        $routes = $this->modulesPath.'/Billing/Routes/index.php';
        $this->assertFileExists($routes);
        $this->assertStringContainsString("Route::prefix('billing')", (string) file_get_contents($routes));

        $this->artisan('module:controller', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();
        $this->artisan('module:dto', ['name' => 'Create Invoice Data', 'module' => 'Billing'])->assertSuccessful();
        $this->artisan('module:model', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();
        $this->artisan('module:service', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();
        $this->artisan('module:repo', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();

        $this->assertFileExists($this->modulesPath.'/Billing/Controllers/InvoiceController.php');
        $this->assertFileExists($this->modulesPath.'/Billing/DTOs/CreateInvoiceData.php');
        $this->assertFileExists($this->modulesPath.'/Billing/Models/Invoice.php');
        $this->assertFileExists($this->modulesPath.'/Billing/Services/InvoiceService.php');
        $this->assertFileExists($this->modulesPath.'/Billing/Interfaces/InvoiceServiceInterface.php');
        $this->assertFileExists($this->modulesPath.'/Billing/Repositories/InvoiceRepository.php');
        $this->assertFileExists($this->modulesPath.'/Billing/Interfaces/InvoiceRepositoryInterface.php');
    }

    public function test_it_protects_existing_modules_and_files_unless_forced(): void
    {
        $this->artisan('make:module', ['name' => 'Billing'])->assertSuccessful();
        $this->artisan('make:module', ['name' => 'billing'])->assertExitCode(1);

        $this->artisan('module:model', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();
        $this->artisan('module:model', ['name' => 'Invoice', 'module' => 'Billing'])->assertExitCode(1);
        $this->artisan('module:model', ['name' => 'Invoice', 'module' => 'Billing', '--force' => true])->assertSuccessful();
    }

    public function test_it_prefers_a_published_stub_when_one_exists(): void
    {
        mkdir($this->modulesPath.'/stubs', 0755, true);
        file_put_contents($this->modulesPath.'/stubs/controller.stub', "<?php\n\nnamespace {{ namespace }};\n\nclass {{ class }}\n{\n    public const PUBLISHED_STUB = true;\n}\n");

        $this->artisan('make:module', ['name' => 'Billing'])->assertSuccessful();
        $this->artisan('module:controller', ['name' => 'Invoice', 'module' => 'Billing'])->assertSuccessful();

        $controller = (string) file_get_contents($this->modulesPath.'/Billing/Controllers/InvoiceController.php');
        $this->assertStringContainsString('public const PUBLISHED_STUB = true;', $controller);
    }

    public function test_it_generates_create_and_update_migrations_and_rejects_conflicting_options(): void
    {
        $this->artisan('make:module', ['name' => 'Billing'])->assertSuccessful();

        $this->artisan('module:migration', [
            'name' => 'create_invoices_table',
            'module' => 'Billing',
            '--create' => 'invoices',
        ])->assertSuccessful();

        $this->artisan('module:migration', [
            'name' => 'add_status_to_invoices',
            'module' => 'Billing',
            '--table' => 'invoices',
        ])->assertSuccessful();

        $migrations = glob($this->modulesPath.'/Billing/Database/Migrations/*.php') ?: [];
        $this->assertCount(2, $migrations);
        $contents = implode("\n", array_map(static fn (string $path): string => (string) file_get_contents($path), $migrations));
        $this->assertStringContainsString("Schema::create('invoices'", $contents);
        $this->assertStringContainsString("Schema::table('invoices'", $contents);

        $this->artisan('module:migration', [
            'name' => 'create_invalid_table',
            'module' => 'Billing',
            '--create' => 'invalid',
            '--table' => 'invalid',
        ])->assertExitCode(1);
    }
}
