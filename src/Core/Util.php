<?php

namespace App\Core;

class Util
{
    public static function runningInConsole()
    {
        return php_sapi_name() === 'cli';
    }
}