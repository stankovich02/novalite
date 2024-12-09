<?php

namespace NovaLite\Exceptions;

class MethodNotSupportedException extends \Exception
{
    public function __construct($message = "Method not supported on this route.", $code = 405, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}