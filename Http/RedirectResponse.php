<?php

namespace NovaLite\Http;

use NovaLite\Application;

class RedirectResponse
{
    public function __construct(string $path = '', int $status = 302)
    {
        http_response_code($status);
        if($path) {
            header('Location: ' . $path);
        }

    }
    public function to(string $routeName) : void
    {
        header('Location: ' . route($routeName));
    }
    public function back() : self
    {
        header('Location: ' . $_SERVER['HTTP_REFERER']);

        return $this;
    }
    public function toURL(string $url) : void
    {
        header('Location: ' . $url);
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

}