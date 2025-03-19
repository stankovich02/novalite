<?php

namespace NovaLite\Routing;

use NovaLite\Application;
use NovaLite\Exceptions\MethodNotSupportedException;
use NovaLite\Http\RedirectResponse;
use NovaLite\Http\Request;
use NovaLite\Http\Response;

class Router{
    private array $routes = [];
    private array $middleware = [];
    private array $groupOptions = [];
    private array $redirectedRoutes = [];
    private Request $request;
    private Response $response;
    public static Router $router;
    private static string $addedRouteToMethod = '';

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        self::$router = $this;
        $this->request = $request;
        $this->response = $response;
    }

    public static function get(string $path, array|string $action) : self
    {
        $path = self::$router->applyGroupOptions($path, $action, 'get');
        self::$router->routes['get'][$path] = $action;
        self::$addedRouteToMethod = 'get';
        return self::$router;
    }
    public static function post(string $path, array|string $action) : self
    {
        $path = self::$router->applyGroupOptions($path, $action);
        self::$router->routes['post'][$path] = $action;
        self::$addedRouteToMethod = 'post';
        return self::$router;
    }
    public static function put(string $path, array|string $action) : self
    {
        $path = self::$router->applyGroupOptions($path, $action);
        self::$router->routes['put'][$path] = $action;
        self::$addedRouteToMethod = 'put';
        return self::$router;
    }
    public static function patch(string $path, array|string $action) : self
    {
        $path = self::$router->applyGroupOptions($path, $action);
        self::$router->routes['patch'][$path] = $action;
        self::$addedRouteToMethod = 'patch';
        return self::$router;
    }
    public static function delete(string $path, array|string $action) : self
    {
        $path = self::$router->applyGroupOptions($path, $action);
        self::$router->routes['delete'][$path] = $action;
        self::$addedRouteToMethod = 'delete';
        return self::$router;
    }
    public static function resource(string $name, string $controller) : void
    {
        if(!str_starts_with($name, '/')){
            $name = '/' . $name;
        }
        self::get($name, [$controller, 'index']);
        self::get($name . '/create', [$controller, 'create']);
        self::post($name, [$controller, 'store']);
        self::get($name . '/{id}', [$controller, 'show']);
        self::get($name . '/{id}/edit', [$controller, 'edit']);
        self::put($name . '/{id}', [$controller, 'update']);
        self::patch($name . '/{id}', [$controller, 'update']);
        self::delete($name . '/{id}', [$controller, 'destroy']);
    }
    public function group(\Closure $callback): void
    {
        $callback();

        if (isset($this->groupOptions['controller'])) {
            $controller = $this->groupOptions['controller'];
            foreach ($this->routes as $method => $routes) {
                foreach ($routes as $path => $callback) {
                    if ($controller === $callback[0] && is_string($callback[1]) && !method_exists($controller,
                            $callback[1])) {
                        throw new \Exception("Method '$callback[1]' does not exist in $controller.");
                    }
                }
            }
        }


        $this->groupOptions = [];

    }
    public static function controller(string $controller) : Router
    {
        self::$router->groupOptions['controller'] = $controller;

        return self::$router;
    }
    public static function middleware(array|string $middlewares) : Router
    {
        self::$router->groupOptions['middlewares'] = $middlewares;

        return self::$router;
    }
    public static function prefix(string $prefix) : Router
    {
        self::$router->groupOptions['prefix'] = $prefix;

        return self::$router;
    }
    public function name(string $name)
    {
        $method = self::$addedRouteToMethod;
        $path = array_key_last($this->routes[$method]);
        $this->routes[$method][$path]['name'] = $name;

        return $this;
    }
    public function where(string $param, string $pattern) : self
    {
        $method = self::$addedRouteToMethod;
        $path = array_key_last($this->routes[$method]);
        $this->routes[$method][$path]['params'][$param] = $pattern;

        return $this;
    }
    public static function redirect(string $from, string $to) : void
    {
        self::$router->redirectedRoutes[$from] = $to;
    }
    public static function view(string $path,string $view ,array $params = []) : void
    {
        $path = self::$router->applyGroupOptions($path, $view);
        self::$router->routes['get'][$path][] = $view;
        if(count($params) > 0){
            self::$router->routes['get'][$path]['params'] = $params;
        }
    }
    public static function match(array $methods, string $path, array|string $action) : void
    {
        foreach ($methods as $method) {
            self::$router->$method($path, $action);
        }
    }
    private function applyGroupOptions(string $path, array|string &$action, string $method = null): string
    {
        if (is_string($action) && isset(self::$router->groupOptions['controller'])) {
            $action = [$this->groupOptions['controller'], $action];
        }
        if (isset($this->groupOptions['prefix'])) {
            $prefix = $this->groupOptions['prefix'] == '/' ? '' : $this->groupOptions['prefix'];
            $path = $prefix . '/' .  ltrim($path, '/');
        }
        if (isset($this->groupOptions['middlewares'])) {
            $middlewares = $this->groupOptions['middlewares'];
            if (!isset($action['middlewares'])) {
                $action['middlewares'] = [];
            }
            $action['middlewares'] = $middlewares;
        }


        return $path;
    }
    public function resolve(){
        $path = $this->request->getPath();
        $method = $this->request->method();
        if($this->request->input('_method')){
            $method = strtolower($this->request->input('_method'));
            unset($_POST['_method']);
        }
        $pathParts = explode('/', trim($path, '/'));
        foreach ($this->redirectedRoutes as $from => $to) {
            $fromParts = explode('/', trim($from, '/'));
            $matched = true;
            foreach ($fromParts as $index => $part) {
                if (preg_match('/^{\w+}$/', $part)) {
                    continue;
                } elseif ($part !== $pathParts[$index]) {
                    $matched = false;
                    break;
                }
            }
            if ($matched) {
                return new RedirectResponse($to);
            }
        }
        $middlewares = require Application::$ROOT_DIR . '/config/middleware.php';

        foreach ($middlewares['onEveryRequest'] as $middlewareClass) {
            $instance = new $middlewareClass();
            $middlewareResult = $instance->handle();
            if ($middlewareResult instanceof RedirectResponse) {
                $middlewareResult->send();
                exit;
            }
        }
        foreach ($this->routes[$method] as $route => $callback) {
            $routeParts = explode('/', trim($route, '/'));

            if (count($routeParts) !== count($pathParts)) {
                continue;
            }
            $matched = true;
            $params = [];

            foreach ($routeParts as $index => $part) {

                if (preg_match('/^{\w+}$/', $part)) {
                    $part = str_replace(['{', '}'], '', $part);
                    $pattern = $callback['params'][$part] ?? '\w+';
                    if (!preg_match("/^$pattern$/", $pathParts[$index])) {
                        return view('_404');
                    }
                    $params[] = $pathParts[$index];
                } elseif ($part !== $pathParts[$index]) {
                    $matched = false;
                    break;
                }
            }
            if ($matched && is_array($callback) && str_contains($callback[0], '\\')) {
                return $this->resolveMiddlewares($callback, $params);
            }

        }
        $action = $this->routes[$method][$path] ?? false;
        if(!$action){
          throw new MethodNotSupportedException();
        }
        if(is_array($action) && str_contains($action[0], '\\')){
            return $this->resolveMiddlewares($action);
        }

        $params = $action['params'] ?? [];
        return view($action[0], $params);
    }
    private function resolveMiddlewares($callback, $params = []) : mixed
    {
        $controller = new $callback[0]();
        $controllerMethod = $callback[1];
        if(count($controller->getMiddlewares()) > 0){
            foreach ($controller->getMiddlewares() as $middleware => $value) {
                if (!is_array($value)) {
                    $middlewareInstance = new $middleware();
                    $middlewareResult = $middlewareInstance->handle();
                    if ($middlewareResult instanceof RedirectResponse) {
                        $middlewareResult->send();
                        exit;
                    }
                } else {
                    if (count($value['only']) > 0) {
                        if (in_array($controllerMethod, $value['only'])) {
                            $middlewareInstance = new $middleware();
                            $middlewareResult = $middlewareInstance->handle();
                            if ($middlewareResult instanceof RedirectResponse) {
                                $middlewareResult->send();
                                exit;
                            }
                        }
                    }
                    if (count($value['except']) > 0) {
                        if (!in_array($controllerMethod, $value['except'])) {
                            $middlewareInstance = new $middleware();
                            $middlewareResult = $middlewareInstance->handle();
                            if ($middlewareResult instanceof RedirectResponse) {
                                $middlewareResult->send();
                                exit;
                            }
                        }
                    }
                }
            }
        }
        if (isset($callback['middlewares'])) {
            if(count($callback['middlewares']) > 0){
                foreach ($callback['middlewares'] as $middleware) {
                    $middleware = new $middleware();
                    $middlewareResult = $middleware->handle();
                    if ($middlewareResult instanceof RedirectResponse) {
                        $middlewareResult->send();
                        exit;
                    }
                }
            }
        }
        return $this->callControllerMethod($controller, $controllerMethod, $params);
    }
    private function callControllerMethod($controller, $method, $params = [])
    {
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        $allParams = ['request' => $this->request, 'response' => $this->response];

        foreach ($params as $index => $value) {
            $allParams['param_' . $index] = $value;
        }

        $finalParams = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if ($typeName === 'NovaLite\Http\Request') {
                    $finalParams[] = $this->request;
                } elseif ($typeName === 'NovaLite\Http\Response') {
                    $finalParams[] = $this->response;
                } elseif (is_subclass_of($typeName, 'NovaLite\Validations\FormRequest')) {
                    $class = new $typeName();
                    $data = $this->request->getAll();
                    $class->validateData();
                    $finalParams[] = $class;
                }
            } else {
                $finalParams[] = array_shift($params);
            }
        }

        return $reflectionMethod->invokeArgs($controller, $finalParams);
    }
    public function getRouteName($name, $parameters) : string|bool
    {
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $callback) {
                if (isset($callback['name']) && $callback['name'] === $name) {
                    $routeParts = explode('/', trim($route, '/'));
                    $path = '';
                    foreach ($routeParts as $index => $part) {
                        if (preg_match('/^{\w+}$/', $part)) {
                            $part = str_replace(['{', '}'], '', $part);
                            $path .= '/' . $parameters[$part];
                        } else {
                            $path .= '/' . $part;
                        }
                    }
                    return $path;
                }
            }
        }

        return false;
    }
    public static function getRoutes() : array
    {
        return self::$router->routes;
    }

}
