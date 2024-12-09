<?php

namespace NovaLite\CLI\Commands;

use Dotenv\Dotenv;
use NovaLite\Application;
use NovaLite\CLI\PrintHelp;

class DbCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Display basic information about your database', 'db [options]');

        $dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
        $dotenv->load();

        $max = 100;

        $dbInfo = [
            'Connection' => $_ENV['DB_CONNECTION'],
            'Name' => $_ENV['DB_NAME'],
            'User' => $_ENV['DB_USER'],
            'Password' => $_ENV['DB_PASSWORD'],
            'Host' => $_ENV['DB_HOST'],
            'Port' => $_ENV['DB_PORT'],
        ];


        echo " \033[32mDatabase information\033[0m\n";
        foreach ($dbInfo as $key => $value) {
            echo " " . $key . " ";
            echo str_repeat(".", $max - strlen(" " . $key . " ") - strlen($value));
            echo " " . $value . "\n";
        }
    }
}