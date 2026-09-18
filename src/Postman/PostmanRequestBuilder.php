<?php

namespace HieuDev92264\LaravelModules\Postman;

use Illuminate\Support\Str;

class PostmanRequestBuilder
{
    public function build(array $route): array
    {
        $request = [
            'name' => $this->resolveName($route),

            'request' => [
                'method' => $route['method'],

                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json',
                        'type' => 'text',
                    ],
                ],

                'url' => $this->buildUrl($route['uri']),
            ],
        ];

        if ($this->requiresAuth($route['middleware'] ?? [])) {
            $auth = $this->buildAuth();

            if ($auth !== null) {
                $request['request']['auth'] = $auth;
            }
        }

        return $request;
    }

    protected function buildAuth(): ?array
    {
        $type = config(
            'modules.postman.auth_type',
            'bearer'
        );

        return match ($type) {
            'bearer' => $this->buildBearerAuth(),

            default => null,
        };
    }

    protected function buildBearerAuth(): array
    {
        $tokenVariable = config(
            'modules.postman.token_variable',
            'token'
        );

        return [
            'type' => 'bearer',

            'bearer' => [
                [
                    'key' => 'token',
                    'value' => '{{' . $tokenVariable . '}}',
                    'type' => 'string',
                ],
            ],
        ];
    }

    protected function resolveName(array $route): string
    {
        return $route['name']
            ?? sprintf(
                '%s %s',
                $route['method'],
                $route['uri']
            );
    }

    protected function buildUrl(string $uri): string
    {
        $baseUrlVariable = config(
            'modules.postman.base_url_variable',
            'base_url'
        );

        $uri = preg_replace_callback(
            '/\{([^}]+)\}/',
            function (array $matches) {
                return ':' . rtrim(
                    $matches[1],
                    '?'
                );
            },
            $uri
        );

        return '{{'
            . $baseUrlVariable
            . '}}/'
            . ltrim($uri, '/');
    }

    protected function requiresAuth(array $middlewares): bool
    {
        $patterns = config(
            'modules.postman.auth_middleware',
            [
                'auth',
                'auth:*',
            ]
        );

        foreach ($middlewares as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $middleware)) {
                    return true;
                }
            }
        }

        return false;
    }
}
