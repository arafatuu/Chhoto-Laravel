<?php

namespace App\Core\Exceptions;

use Exception;

class UnresolvedParameterException extends Exception
{
    public function __construct($parameterName, $code = 0, Exception $previous = null)
    {
        $message = sprintf('The parameter [%s] could not be resolved.', $parameterName);
        parent::__construct($message, $code, $previous);
    }
}
