<?php

namespace NovaLite\Http;

use NovaLite\Application;
use NovaLite\Database\Database;
use NovaLite\Validations\ValidationError;

class Request
{
    protected function customMessages() : array
    {
        return [];
    }
    private array $data = [];
    private array $errors = [];
    private string $currentField;
    private array $messages = [
        'alpha' => 'This field must contain only letters',
        'alpha_num' => 'This field must contain only letters and numbers',
        'array' => 'This field must be an array',
        'after' => '',
        'before' => '',
        'between' => '',
        'boolean' => 'This field must be a boolean',
        'date' => 'This field must be a date',
        'decimal' => 'This field must be a decimal number',
        'email' => 'This field must be a valid email address',
        'file' => 'This field must be a file',
        'integer' => 'This field must be an integer',
        'json' => 'This field must be a valid JSON',
        'max' => '',
        'mimes' => '',
        'min' => '',
        'required' => 'This field is required',
        'numeric' => 'This field must be a number',
        'regex' => 'This field is in invalid format',
        'same' => '',
        'string' => 'This field must be a string',
        'unique' => 'This field must be unique'
    ];
    private function changeDefaultMessage($ruleName, $rule) : void
    {
        switch ($ruleName){
            case 'after':
                $this->messages[$ruleName] = 'This field must be after the date: ' . str_replace('after:', '', $rule);
                break;
            case 'before':
                $this->messages[$ruleName] = 'This field must be before the date: ' . str_replace('before:', '', $rule);
                break;
            case 'between':
                $this->messages[$ruleName] = 'This field must be between ' . explode(',', str_replace('between:', '', $rule))[0] . ' and ' . explode(',', str_replace('between:', '', $rule))[1];
                break;
            case 'max':
                $this->messages[$ruleName] = 'This field must be less than or equal to ' . str_replace('max:', '', $rule);
                break;
            case 'mimes':
                $this->messages[$ruleName] = 'This field must be of type ' . str_replace('mimes:', '', $rule);
                break;
            case 'min':
                $this->messages[$ruleName] = 'This field must be greater than or equal to ' . str_replace('min:', '', $rule);
                break;
            case 'same':
                $this->messages[$ruleName] = 'This field must be the same as ' . str_replace('same:', '', $rule);
                break;
        }
    }
    public function validate($data) : bool
    {
        $this->data = $this->getAll();
        foreach ($data as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $this->currentField = $field;

            foreach (explode('|', $fieldRules) as $rule)
            {
                if(!$this->applyRule($value, $rule)) {
                    switch ($rule) {
                        case 'alpha':
                            $this->checkCustomMessage('alpha');
                            break;
                        case 'alpha_num':
                            $this->checkCustomMessage('alpha_num');
                            break;
                        case 'array':
                            $this->checkCustomMessage('array');
                            break;
                        case str_contains($rule, 'after'):
                            $this->changeDefaultMessage('after', $rule);
                            $this->checkCustomMessage('after');
                            break;
                        case str_contains($rule, 'before'):
                            $this->changeDefaultMessage('before', $rule);
                            $this->checkCustomMessage('before');
                            break;
                        case str_contains($rule, 'between'):
                            $this->changeDefaultMessage('between', $rule);
                            $this->checkCustomMessage('between');
                            break;
                        case 'boolean':
                            $this->checkCustomMessage('boolean');
                            break;
                        case 'date':
                            $this->checkCustomMessage('date');
                            break;
                        case 'decimal':
                            $this->checkCustomMessage('decimal');
                            break;
                        case 'email':
                            $this->checkCustomMessage('email');
                            break;
                        case 'file':
                            $this->checkCustomMessage('file');
                            break;
                        case 'integer':
                            $this->checkCustomMessage('integer');
                            break;
                        case 'json':
                            $this->checkCustomMessage('json');
                            break;
                        case str_contains($rule, 'max'):
                            $this->changeDefaultMessage('max', $rule);
                            $this->checkCustomMessage('max');
                            break;
                        case str_contains($rule, 'mimes'):
                            $this->changeDefaultMessage('mimes', $rule);
                            $this->checkCustomMessage('mimes');
                            break;
                        case str_contains($rule, 'min'):
                            $this->changeDefaultMessage('min', $rule);
                            $this->checkCustomMessage('min');
                            break;
                        case 'required':
                            $this->checkCustomMessage('required');
                            break;
                        case 'numeric':
                            $this->checkCustomMessage('numeric');
                            break;
                        case str_contains($rule, 'regex'):
                            $this->checkCustomMessage('regex');
                            break;
                        case str_contains($rule, 'same'):
                            $this->changeDefaultMessage('same', $rule);
                            $this->checkCustomMessage('same');
                            break;
                        case 'string':
                            $this->checkCustomMessage('string');
                            break;
                        case str_contains($rule, 'unique'):
                            $this->checkCustomMessage('unique');
                            break;
                    }
                }
            }
        }
        if(count($this->errors))
        {
            Application::$app->session->markValidationFailed();

            Application::$app->session->setErrors(new ValidationError($this->errors));
            Application::$app->session->setOldData($this->data);
            return false;
        }
        else{

            Application::$app->session->clearFormData();
            return true;
        }
    }
    private function applyRule($value,$rule) : bool
    {
        switch ($rule) {
            case 'alpha':
                return ctype_alpha($value);
            case 'alpha_num':
                return ctype_alnum($value);
            case 'array':
                return is_array($value);
            case str_contains($rule, 'after'):
                $checkValue = explode(':', $rule)[1];
                if (is_string($checkValue)) {
                    return strtotime($value) > strtotime($this->data[$checkValue]);
                }
                return strtotime($value) > strtotime($checkValue);
            case str_contains($rule, 'before'):
                $checkValue = explode(':', $rule)[1];
                if (is_string($checkValue)) {
                    return strtotime($value) < strtotime($this->data[$checkValue]);
                }
                return strtotime($value) < strtotime($checkValue);
            case str_contains($rule, 'between'):
                $min = explode(',', str_replace('between:', '', $rule))[0];
                $max = explode(',', str_replace('between:', '', $rule))[1];
                return $value >= $min && $value <= $max;
            case 'boolean':
                return is_bool($value);
            case 'date':
                return strtotime($value) !== false;
            case 'decimal':
                return is_float($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'file':
                return is_file($value);
            case 'integer':
                return is_int($value);
            case 'json':
                return is_array(json_decode($value, true));
            case str_contains($rule, 'max'):
                $max = str_replace('max:', '', $rule);
                return $this->checkSizeOfValue($value, $max, 'max');
            case 'mimes':
                $mimes = explode(',', str_replace('mimes:', '', $rule));
                $extension = pathinfo($value, PATHINFO_EXTENSION);
                return in_array($extension, $mimes);
            case str_contains($rule, 'min'):
                $min = str_replace('min:', '', $rule);
                return $this->checkSizeOfValue($value, $min, 'min');
            case 'required':
                return !empty($value);
            case 'nullable':
                return true;
            case 'numeric':
                return is_numeric($value);
            case str_contains($rule, 'regex'):
                $pattern = str_replace('regex:', '', $rule);
                return preg_match($pattern, $value);
            case str_contains($rule, 'same'):
                $field = str_replace('same:', '', $rule);
                return $value === $this->data[$field];
            case str_contains($rule, 'size'):
                $size = explode(':', $rule)[1];
                return $this->checkSizeOfValue($value, $size);
            case str_contains($rule, 'unique'):
                $data = explode(':', $rule)[1];
                if (!str_contains($data, ',')) {
                    $table = $data;
                    $column = $this->currentField;
                } else {
                    $table = explode(',', $data)[0];
                    $column = explode(',', $data)[1];
                }
                $pdo = Database::getInstance();
                $statement = $pdo->prepare("SELECT $column FROM $table WHERE $column = :value");
                $statement->bindValue(':value', $value);
                $statement->execute();
                return $statement->rowCount() === 0;
            default:
                return true;
        }
    }
    private function checkCustomMessage($rule) : void
    {
        $customMessages = $this->customMessages();
        if(array_key_exists($this->currentField . '.' . $rule, $customMessages))
        {
            $this->errors[$this->currentField][] = $customMessages[$this->currentField . '.' . $rule];
        }
        $this->errors[$this->currentField][] = $this->messages[$rule];
    }
    private function checkSizeOfValue($value, $size, $option = null) : bool
    {
        if(is_array($value))
        {
            if($option === 'max')
            {
                return count($value) <= $size;
            }
            if($option === 'min')
            {
                return count($value) >= $size;
            }
            return count($value) === $size;
        }
        if(is_string($value))
        {
            if($option === 'max')
            {
                return strlen($value) <= $size;
            }
            if($option === 'min')
            {
                return strlen($value) >= $size;
            }
            return strlen($value) === $size;
        }
        if(is_numeric($value))
        {
            if($option === 'max')
            {
                return $value <= $size;
            }
            if($option === 'min')
            {
                return $value >= $size;
            }
            return $value === $size;
        }
        if(is_file($value))
        {
            if($option === 'max')
            {
                return filesize($value) <= $size;
            }
            if($option === 'min')
            {
                return filesize($value) >= $size;
            }
            return filesize($value) === $size;
        }
        return false;
    }
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if (!$position) {
            return $path;
        }

        return substr($path, 0, $position);
    }
    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool
    {
        return $this->method() === 'get';
    }

    public function isPost(): bool
    {
        return $this->method() === 'post';
    }

    public function getAll(): array
    {
        $method = $this->method();

        if ($method === 'get') {
            return $_GET;
        }

        if (in_array($method, ['post', 'patch', 'put'])) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            return is_array($data) ? $data : $_POST;
        }

        return [];
    }

    public function input(string $key)
    {
        return $this->getAll()[$key] ?? null;
    }
    public function query(string $key)
    {
        return $_GET[$key] ?? null;
    }
    public function file(string $key): UploadedFile
    {
        return new UploadedFile($_FILES[$key]);
    }

    public function server(): array
    {
        return $_SERVER;
    }

    public function has(array|string $keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->getAll())) {
                return false;
            }
        }
        return true;
    }

    public function hasAny(array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->getAll())) {
                return true;
            }
        }
        return false;
    }

    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]);
    }

    public function cookie(string $key = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }
        return $_COOKIE[$key] ?? null;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function notInclude(string $key): bool
    {
        return !array_key_exists($key, $this->getAll());
    }

    public function empty(string $key): bool
    {
        return empty($this->getAll()[$key]);
    }

    public function isSecure(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public function notEmpty(string $key): bool
    {
        return !empty($this->getAll()[$key]);
    }

    public function headers(): array
    {
        return getallheaders();
    }

    public function header(string $key): string
    {
        return getallheaders()[$key] ?? '';
    }

    public function hasHeader(string $key): bool
    {
        return isset(getallheaders()[$key]);
    }

    public function path(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function url(): string
    {
        return $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function host(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}