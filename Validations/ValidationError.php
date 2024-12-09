<?php

namespace NovaLite\Validations;

class ValidationError
{
    private array $errors = [];
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }
    public function all() : array
    {
        return $this->errors;
    }
    public function first($field) : string
    {
        return $this->errors[$field][0] ?? '';
    }
    public function has($field) : bool
    {
        return isset($this->errors[$field]);
    }
}