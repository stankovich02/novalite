<?php

namespace NovaLite\CLI\Commands;

use NovaLite\Application;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;
use NovaLite\Routing\Router;

class RouteListCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $options = [
            '--method=METHOD' => 'Filter the routes by method',
            '--name=NAME' => 'Filter the routes by name',
            '--path=PATH' => 'Filter the routes by path',
        ];
        $max = 200;
        $this->printHelp($args, 'List all registered routes', 'route:list', $options);
        $config = [
            'db' => Config::get('database'),
            'log' => Config::get('logging')
        ];

        require_once dirname(__DIR__,3) . '/bootstrap/app.php';
        require_once dirname(__DIR__,3) . '/routing/routes.php';

        $routes = Router::getRoutes();
        echo " \033[32mMethod\t\tPath\033[0m ";
        echo str_repeat(".", $max - strlen(" Method\t\tPath ") - strlen(" Route name > Controller@Action"));
        echo " \033[32mRoute name > Controller@Action\033[0m\n";
        if(isset($args[0])){
            if(str_contains($args[0], '--method=')){
                $method = strtolower(explode('=', $args[0])[1]);
                $routes = array_filter($routes, function($key) use ($method){
                    return $key === $method;
                }, ARRAY_FILTER_USE_KEY);
                $this->printRoutes($routes);
            }
            else if(str_contains($args[0], '--name=')){
                $name = strtolower(explode('=', $args[0])[1]);
                $namedRoutes = [];
                foreach ($routes as $method => $route) {
                    foreach ($route as $path => $callback){
                        if(isset($callback['name']) && str_contains($callback['name'], $name)){
                            $namedRoutes[$method][$path] = $callback;
                        }
                    }
                }
                $this->printRoutes($namedRoutes);
            }
            else if(str_contains($args[0], '--path=')){
                $pathName = strtolower(explode('=', $args[0])[1]);
                $pathRoutes = [];
                foreach ($routes as $method => $route) {
                    foreach ($route as $path => $callback){
                        if(str_contains($path, $pathName)){
                            $pathRoutes[$method][$path] = $callback;
                        }
                    }
                }
                $this->printRoutes($pathRoutes);
            }
            else{
                $this->printRoutes($routes);
            }
        }
        else{
            $this->printRoutes($routes);
        }

    }
    private function stripAnsiCodes(string $text): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $text);
    }
    private function printRoutes($routes) : void
    {
        foreach ($routes as $method => $route) {
            $method = strtoupper($method);
            switch ($method){
                case 'GET':
                    $method = "\033[34m$method\033[0m";
                    break;
                case 'POST':
                case 'PUT':
                case 'PATCH':
                    $method = "\033[33m$method\033[0m";
                    break;
                case 'DELETE':
                    $method = "\033[31m$method\033[0m";
                    break;
                default:
                    $method = "\033[33m$method\033[0m";
            }
            foreach ($route as $path => $callback){
                echo " $method\t\t$path ";
                if(isset($callback[1])){
                    $routeName = $callback['name'] ? $callback['name'] . ' > ' : '';
                    $description = $routeName . $callback[0] . '@' . $callback[1];
                }
                else{
                    $routeName = $callback['name'] ?? '';
                    $description = $routeName;
                }

                $visibleMethodLength = strlen($this->stripAnsiCodes($method));
                $visiblePathLength = strlen($path);
                $visibleDescriptionLength = strlen($this->stripAnsiCodes($description));
                $remainingDots = 200 - ($visibleMethodLength + $visiblePathLength +
                        $visibleDescriptionLength + 8);

                echo str_repeat('.', $remainingDots);
                echo " $description\n";
            }

        }
    }
}