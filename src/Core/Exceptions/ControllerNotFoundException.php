<?php

namespace App\Core\Exceptions;

use Exception;

class ControllerNotFoundException extends Exception
{
    public function __construct($controller, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Controller [%s] not found.', $controller);
        parent::__construct($message, $code, $previous);
    }
}
