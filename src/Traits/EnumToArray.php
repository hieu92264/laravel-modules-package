<?php

namespace HieuDev92264\LaravelModules\Traits;

trait EnumToArray
{
    public static function value(): array
    {
        return array_column(self::case(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::case(), 'name');
    }
}
