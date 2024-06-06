<?php

namespace App\Core\Exceptions;

use Exception;

class DuplicateDependencyException extends Exception
{
    public function __construct($dependencyName, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Dependency [%s] already exists.', $dependencyName);
        parent::__construct($message, $code, $previous);
    }
}
