<?php

namespace HieuDev92264\LaravelModules\Postman;

use Illuminate\Routing\Route;

class ModuleResolver
{
    public function resolve(Route $route): ?string
    {
        $action = $route->getActionName();

        if ($action === 'Closure') {
            return null;
        }

        $namespace = trim((string) config('modules.namespace', 'App\\Modules'), '\\');

        if ($namespace === '') {
            return null;
        }

        $pattern = '/^'.preg_quote($namespace, '/').'\\\\([^\\\\]+)\\\\/';

        if (preg_match($pattern, $action, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
