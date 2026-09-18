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

        if (
            preg_match(
                '/^App\\\\Modules\\\\([^\\\\]+)\\\\/',
                $action,
                $matches
            )
        ) {
            return $matches[1];
        }

        return null;
    }
}
