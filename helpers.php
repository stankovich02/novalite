<?php

use NovaLite\Application;
use NovaLite\Http\RedirectResponse;
use NovaLite\Http\Response;
use NovaLite\Sessions\Session;

if (!function_exists('view')) {
    /**
     * Render the view.
     *
     * @param  string  $viewPath
     * @param  array  $params
     * @return string
     */
    function view(string $viewPath, array $params = []) : string
    {
        return Application::$view->renderView($viewPath, $params);
    }
}
if (!function_exists('asset')) {
    /**
     * Get the asset path.
     *
     * @param  string  $path
     * @return string
     */
    function asset(string $path) : string
    {
        $schema = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        return $schema . "://" . $_SERVER['HTTP_HOST'] . '/' . ltrim($path, '/');
    }

}
if (!function_exists('redirect')) {
    /**
     * Get the redirect response instance.
     *
     * @param  string  $path
     * @param  int  $status
     * @return RedirectResponse
     */
    function redirect(string $path = '', int $status = 302) : RedirectResponse
    {
        if($path){
            http_response_code($status);
            header('Location: ' . $path);
        }
        return new RedirectResponse($path, $status);
    }
}
if (!function_exists('route')) {
    /**
     * Get the route path.
     *
     * @param  string  $name
     * @return string
     */
    function route(string $name, array $parameters = null) : string
    {
        return Application::$app->router->getRouteName($name, $parameters);
    }
}
if (!function_exists('old')) {
    /**
     * Get the old input value.
     *
     * @param  string  $key
     * @return string
     */
    function old(string $key) : string
    {
       return $_SESSION['old'][$key] ?? '';
    }
}
if (!function_exists('response')) {
    /**
     * Get the response instance.
     *
     * @param  string  $content
     * @param  int  $status
     * @return Response
     */
    function response(string $content = '', int $status = 200) : Response
    {
        return new Response($content, $status);
    }
}
if (!function_exists('session')) {
    /**
     * Get the session instance.
     *
     * @return Session
     */
    function session() : Session
    {
        return Application::$app->session;
    }
}
if (!function_exists('public_path')) {
    /**
     * Get the public path.
     *
     * @param  string  $path
     * @return string
     */
    function public_path(string $path) : string
    {
        return Application::$ROOT_DIR . '/public/' . $path;
    }
}
if (!function_exists('database_path')) {
    /**
     * Get the database path.
     *
     * @param  string  $path
     * @return string
     */
    function database_path(string $path) : string
    {
        return Application::$ROOT_DIR . '/database/' . $path;
    }
}
if(!function_exists('env')){
    /**
     * Get the environment variable.
     *
     * @param  string  $key
     * @return string|null
     */
    function env(string $key) : ?string
    {
        return $_ENV[$key] ?? null;
    }

}