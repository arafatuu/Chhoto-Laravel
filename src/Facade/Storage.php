<?php

namespace App\Facade;

use App\Core\Facade;

class Storage extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'storage';
    }
}