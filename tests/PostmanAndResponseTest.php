<?php

declare(strict_types=1);

namespace HieuDev92264\LaravelModules\Tests;

use HieuDev92264\LaravelModules\Postman\ModuleResolver;
use HieuDev92264\LaravelModules\Postman\PostmanCollectionBuilder;
use HieuDev92264\LaravelModules\Postman\PostmanRequestBuilder;
use HieuDev92264\LaravelModules\Postman\RouteScanner;
use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use Symfony\Component\HttpFoundation\Response;

class PostmanAndResponseTest extends TestCase
{
    public function test_it_builds_postman_requests_for_module_routes(): void
    {
        $route = new Route(['GET'], 'catalog/{product?}', static fn () => null);
        $route->setAction([
            'uses' => 'App\\Modules\\Catalog\\Controllers\\CatalogController@index',
            'controller' => 'App\\Modules\\Catalog\\Controllers\\CatalogController@index',
        ]);

        $this->assertSame('Catalog', (new ModuleResolver())->resolve($route));

        $builder = new PostmanRequestBuilder();
        $request = $builder->build([
            'method' => 'GET',
            'uri' => 'catalog/{product?}',
            'middleware' => ['auth:sanctum'],
        ]);

        $this->assertSame('{{api_url}}/catalog/:product', $request['request']['url']);
        $this->assertSame('{{api_token}}', $request['request']['auth']['bearer'][0]['value']);

        $collection = (new PostmanCollectionBuilder($builder))->build([[
            'module' => 'Catalog',
            'method' => 'GET',
            'uri' => 'catalog/{product?}',
            'middleware' => ['auth:sanctum'],
        ]]);

        $this->assertSame('Package Test API', $collection['info']['name']);
        $this->assertSame('https://package.test/v1', $collection['variable'][0]['value']);
        $this->assertSame('Catalog', $collection['item'][0]['name']);
    }

    public function test_it_returns_the_documented_api_response_contract(): void
    {
        $this->app->instance('request', Request::create('/api/billing/invoices'));
        $responder = new class
        {
            use ApiResponse;

            public function respond(): \Illuminate\Http\JsonResponse
            {
                return $this->success(['id' => 1], 'Created', Response::HTTP_CREATED);
            }
        };

        $payload = $responder->respond()->getData(true);

        $this->assertSame('Created', $payload['message']);
        $this->assertSame(201, $payload['status_code']);
        $this->assertSame(['id' => 1], $payload['metadata']);
        $this->assertSame('/api/billing/invoices', $payload['path']);
        $this->assertArrayHasKey('timestamp', $payload);
    }

    public function test_it_scans_module_routes_and_writes_a_postman_collection(): void
    {
        RouteFacade::get('catalog/{product?}', 'App\\Modules\\Catalog\\Controllers\\CatalogController@index')
            ->middleware('auth:sanctum')
            ->name('catalog.show');

        $routes = (new RouteScanner(new ModuleResolver()))->scan();

        $this->assertSame('Catalog', $routes[0]['module']);
        $this->assertSame('catalog/{product?}', $routes[0]['uri']);

        $output = 'postman-package-test-'.uniqid().'.json';
        $path = base_path($output);

        try {
            $this->artisan('module:postman', ['--output' => $output])->assertSuccessful();

            $this->assertFileExists($path);
            $collection = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('Catalog', $collection['item'][0]['name']);
        } finally {
            File::delete($path);
        }
    }
}
