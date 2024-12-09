<?php

namespace NovaLite\Http;

use NovaLite\Http\Middlewares\MiddlewareInterface;

class Controller
{
    private array $middlewares = [];

    public function registerMiddleware(string $middleware) : self
    {
        $this->middlewares[$middleware] = true;

        return $this;
    }
    public function only(array $actions) : void
    {
        $keys = array_keys($this->middlewares);
        $lastKey = end($keys);

        if (!is_array($this->middlewares[$lastKey])) {
            $this->middlewares[$lastKey] = ['only' => $actions, 'except' => []];
        }
    }
    public function except(array $actions) : void
    {
        $keys = array_keys($this->middlewares);
        $lastKey = end($keys);

        if (!is_array($this->middlewares[$lastKey])) {
            $this->middlewares[$lastKey] = ['only' => [], 'except' => $actions];
        }
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}