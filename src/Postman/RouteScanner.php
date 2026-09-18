<?php

namespace HieuDev92264\LaravelModules\Postman;

use Illuminate\Routing\Route;

class RouteScanner
{
    public function __construct(protected ModuleResolver $moduleResolver) {}

    public function scan(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $module = $this->moduleResolver->resolve($route);

            if (!$module) {
                continue;
            }

            $methods = array_filter(
                $route->methods(),
                fn(string $method) => !in_array(
                    $method,
                    ['HEAD', 'OPTIONS']
                )
            );

            foreach ($methods as $method) {
                $routes[] = [
                    'module' => $module,
                    'method' => $method,
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->gatherMiddleware(),
                ];
            }
        }

        return $routes;
    }
}
