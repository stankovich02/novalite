<?php

namespace NovaLite\CLI\Commands;

use NovaLite\Application;

class ServeCommand implements CommandInterface
{
    public function handle($args) : void
    {
        $host = 'localhost';
        $port = 8080;

        if(isset($args[0])) {
           if(str_contains($args[0], '--host')) {
                $host = explode('=', $args[0])[1];
           }
          if(str_contains($args[0], '--port')) {
             $port = explode('=', $args[0])[1];
          }
          if(str_contains($args[0], '--help'))
             echo "\033[33mDescription:\033[0m\n";
             echo "  Serve the application on the PHP development server\n\n";
             echo "\033[33mUsage:\033[0m\n";
             echo "  serve [options]\n\n";
             echo "\033[33mOptions:\033[0m\n";
             echo "\t\033[32m--host=HOST\033[0m";
             echo str_pad('', 20 - strlen('--host=HOST'));
             echo "The host address to serve the application on\n";
             echo "\t\033[32m--port=PORT\033[0m";
             echo str_pad('', 20 - strlen('--port=PORT'));
             echo "The port to serve the application on\n";
             exit(0);
        }
        $publicDir = Application::$ROOT_DIR . '/public';

        if (!is_dir($publicDir)) {
            echo "Error: The public folder does not exist.\n";
            exit(1);
        }

        echo "Starting development server at http://$host:$port\n";
        echo "Press Ctrl+C to stop the server.\n";


        $command = "php -S $host:$port -t $publicDir";
        passthru($command);
    }
}