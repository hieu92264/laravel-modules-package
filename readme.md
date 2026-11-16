# Laravel Modules Generator

Laravel Modules Generator creates a consistent module structure, module artifacts, migrations, and Postman collections for Laravel applications.

## Requirements

- PHP 8.2+ for Laravel 10–12; PHP 8.3+ for Laravel 13.
- Laravel 10, 11, 12, or 13.
- The GD extension (`ext-gd`) when using WebP conversion.

Install stable tagged releases in production:

```bash
composer require hieu-dev-92264/laravel-modules:^1.0
```

For a private VCS repository, add its real Git URL under `repositories` in the consuming application's `composer.json` before requiring the package.

## Configuration and stubs

```bash
php artisan vendor:publish --tag=modules-config
php artisan vendor:publish --tag=modules-stubs
```

`modules.php` configures the module base path, generated namespace, aliases, API prefix, and the location of published stubs. Published stubs take precedence one file at a time; package stubs remain the fallback.

## Commands

```bash
php artisan make:module Billing
php artisan module:controller InvoiceController Billing
php artisan module:dto CreateInvoiceData Billing
php artisan module:model Invoice Billing
php artisan module:service InvoiceService Billing
php artisan module:repo InvoiceRepository Billing
php artisan module:migration create_invoices_table Billing --create=invoices
php artisan module:migration add_status_to_invoices Billing --table=invoices
php artisan module:postman --output=postman_api.json
```

Module and class names are normalized to PascalCase and must contain only letters and numbers. Migration names are normalized to lowercase snake case. A generator does not overwrite existing files unless `--force` is supplied. Migration commands reject using `--create` and `--table` together.

## Postman generation

`module:postman` scans registered routes whose controller namespace matches `modules.namespace`, groups them by module, and writes a Postman Collection v2.1 JSON file. Routes using configured auth middleware receive configured bearer-token authentication.

```php
// config/modules.php
'postman' => [
    'auth_middleware' => ['auth', 'auth:*'],
    'auth_type' => 'bearer',
    'token_variable' => 'access_token',
    'base_url_variable' => 'base_url',
],
```

The collection base URL combines `app.url` and `modules.api_prefix`; optional Laravel route parameters become Postman `:parameters`.

## File uploads and WebP

`HieuDev92264\LaravelModules\Common\FileHelper` stores an `UploadedFile` to a local directory and returns metadata:

```php
use HieuDev92264\LaravelModules\Common\FileHelper;

$file = FileHelper::save($request->file('document'), storage_path('app/uploads'));
$image = FileHelper::saveAsWebp($request->file('avatar'), storage_path('app/uploads'));

// path, filename, original_name, extension, mime_type, size
```

`saveAsWebp()` accepts valid images supported by the configured `buglinjo/laravel-webp` driver. Publish and configure that dependency before using conversion:

```bash
php artisan vendor:publish --provider="Buglinjo\LaravelWebp\WebpServiceProvider" --tag=config
```

The default `php-gd` driver requires `ext-gd`; alternatively configure the dependency's `cwebp` driver and executable path in `config/laravel-webp.php`.

## API responses

The generated controller trait returns a consistent response contract:

```json
{
  "message": "Success",
  "status_code": 200,
  "metadata": null,
  "path": "/api/billing/invoices",
  "timestamp": "2026-11-16T00:00:00.000000Z"
}
```

Exception diagnostics are added only while `app.debug=true`; trace arguments are never returned.

## Development checks

This library intentionally does not commit `composer.lock`; CI resolves dependencies for each supported Laravel version.

```bash
composer validate --strict
composer dump-autoload --optimize --strict-psr
composer test:lint
composer test
composer audit
```

GitHub Actions runs this suite for Laravel 10–13. Laravel 13 is tested with PHP 8.3; Laravel 10–12 are tested with PHP 8.2.

Laravel 10 is tested for package compatibility, but its dependency line no longer receives fixes for current framework advisories. The CI security gate therefore audits the current Laravel 13 dependency baseline; Laravel 10 consumers should plan an application upgrade.
