<?php

namespace NovaLite\Http;

use NovaLite\Application;

class RedirectResponse
{
    private string $path = '';
    private int $statusCode = 302;
    public function __construct(string $path, int $status)
    {
        if($path) {
            $this->$path = $path;
        }

    }
    public function to(string $routeName) : void
    {
        $this->path = route($routeName);
    }
    public function back() : self
    {
        $this->path = $_SERVER['HTTP_REFERER'];

        return $this;
    }
    public function toURL(string $url) : void
    {
        $this->path = $url;
    }
    public function withLastInputs() : void
    {
        $_SESSION['old'] = $_POST;
    }
    public function with(string $key,string $value) : self
    {
        Application::$app->session->flash($key, $value);

        return $this;
    }
    public function send(): void
    {
        http_response_code($this->statusCode);
        header("Location: " . $this->path);
        exit;
    }

}