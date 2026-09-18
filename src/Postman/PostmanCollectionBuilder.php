<?php

namespace HieuDev92264\LaravelModules\Postman;

class PostmanCollectionBuilder
{
    public function __construct(protected PostmanRequestBuilder $postmanRequestBuilder) {}

    public function build(array $routes): array
    {
        $modules = [];

        foreach ($routes as $route) {
            $module = $route['module'];

            $modules[$module][] = $this->postmanRequestBuilder->build($route);
        }

        $items = [];
        foreach ($modules as $module => $requests) {
            $items[] = [
                'name' => $module,
                'item' => $requests
            ];
        }

        return [
            'info' => [
                'name' => config('app.name', 'Laravel API'),

                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],

            'variable' => [
                [
                    'key' => config('modules.postman.base_url_variable', 'base_url'),
                    'value' => $this->baseUrl(),
                    'type' => 'string'
                ],
                [
                    'key' => config('modules.postman.token_variable', 'access_token'),
                    'value' => '',
                    'type' => 'string'
                ]
            ],

            'item' => $items
        ];
    }

    private function baseUrl(): string
    {
        $applicationUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $apiPrefix = trim((string) config('modules.api_prefix', 'api'), '/');

        return $apiPrefix === ''
            ? $applicationUrl
            : $applicationUrl.'/'.$apiPrefix;
    }
}
