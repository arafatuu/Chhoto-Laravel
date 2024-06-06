<?php

namespace App\Facade;

use App\Core\Facade;

class Route extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'router';
    }
}