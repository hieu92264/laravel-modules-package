<?php

namespace HieuDev92264\LaravelModules\Traits;

trait EnumToArray
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
