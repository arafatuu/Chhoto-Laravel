<?php

use App\Core\Util;

if (!function_exists('dd')) {
    function dd(...$args)
    {
        if (!Util::runningInConsole()) {
            header('Content-Type: application/json');
        }

        foreach ($args as $arg) {
            print_r($arg);
            echo PHP_EOL;
        }

        die();
    }
}
