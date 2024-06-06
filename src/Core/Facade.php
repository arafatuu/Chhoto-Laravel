<?php

namespace App\Core;

class Facade
{
    public static $app;

    public static function __callStatic($method, $args)
    {
        $abstract = static::getFacadeAccessor();
        $instance = static::$app->make($abstract);
        return $instance->$method(...$args);
    }
}