<?php

namespace NovaLite\Exceptions;

class ModelNotFoundException extends \Exception
{
    public function __construct($message = "Model not found.", $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}