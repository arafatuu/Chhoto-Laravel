<?php

namespace App\Facade;

use App\Core\Facade;

class DB extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'database';
    }
}
