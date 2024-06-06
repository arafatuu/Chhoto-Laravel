<?php

namespace App\Core\Exceptions;

use Exception;

class MethodNotFoundException extends Exception
{
    public function __construct($method, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Method [%s] not found.', $method);
        parent::__construct($message, $code, $previous);
    }
}
