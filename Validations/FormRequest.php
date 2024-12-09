<?php

namespace NovaLite\Validations;

use NovaLite\Http\Request;

abstract class FormRequest extends Request
{
    abstract protected function rules() : array;
    public function validateData() : void
    {
        $this->validate($this->rules());
    }
}