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
            $this->path = $path;
        }
        $this->statusCode = $status;
    }
    public function to(string $routeName) : self
    {
        $this->path = route($routeName);

        return $this;
    }
    public function back() : self
    {
        $this->path = $_SERVER['HTTP_REFERER'];

        return $this;
    }
    public function toURL(string $url) : void
    {
        $this->path = $url;

        $this->send();
    }
    public function withLastInputs() : self
    {
        $_SESSION['old'] = $_POST;

        return $this;
    }
    public function with(string $key,string $value) : self
    {
        Application::$app->session->flash($key, $value);

        return $this;
    }
    public function send(): void
    {
        session_write_close();
        http_response_code($this->statusCode);
        header("Location: " . $this->path);
    }

}