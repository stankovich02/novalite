<?php

namespace NovaLite\CLI\Commands\Make;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;

class MiddlewareCommand implements CommandInterface
{
    use PrintHelp;
    use PrintCard;
    public function handle($args): void
    {
        $this->printHelp($args, 'Create a new HTTP middleware class', 'make:middleware [middleware_name]');
        $middlewareName =  count($args) < 1 ? readline(" Enter middleware name: ") : $args[0];
        $middlewarePath = 'app/Middlewares/' . $middlewareName . '.php';
        if (!is_dir('app/Middlewares')) {
            mkdir('app/Middlewares');
        }
        if (file_exists($middlewarePath)) {
            $this->printCard('ERROR', 'Middleware already exists.');
            return;
        }
        $middlewareContent = "<?php\n\nnamespace App\Middlewares;\n\nuse NovaLite\Http\Middlewares\MiddlewareInterface;\n\nclass $middlewareName implements MiddlewareInterface\n{\n\tpublic function handle()\n\t{\n\t\t//\n\t}\n}\n";
        file_put_contents($middlewarePath, $middlewareContent);
        $middlewarePath = realpath($middlewarePath);
        $this->printCard('INFO', "Middleware \033[1m[$middlewarePath]\033[0m created successfully.");
    }
}