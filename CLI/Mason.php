<?php

namespace NovaLite\CLI;

use NovaLite\Application;
use NovaLite\Config\Config;

class Mason
{
    public static function resolve($argv) : void
    {
        $command = $argv[1] ?? null;

        if (!$command || $command === 'list') {
            $commands = [
                'about' => 'Display basic information about your application',
                'db' => 'Display database information',
                'env' => 'Display the current framework environment',
                'list' => 'List commands',
                'migrate' => 'Run the database migrations',
                'serve' => 'Serve the application on the PHP development server',
            ];
            $options = [
                '--help' => 'Display help for the given command.',
                '--version' => 'Display this application version.',
            ];
            $groupedCommands = [
                'db' => [
                    'db:seed' => 'Seed the database with records',
                    'db:table' => 'Display information about the given database table',
                ],
                'make' => [
                    'make:controller' => 'Create a new controller class',
                    'make:middleware' => 'Create a new middleware class',
                    'make:migration' => 'Create a new migration file',
                    'make:model' => 'Create a new model class',
                    'make:request' => 'Create a new form request class',
                    'make:seeder' => 'Create a new seeder class',
                ],
                'migrate' => [
                    'migrate:fresh' => 'Drop all tables and re-run all migrations',
                    'migrate:refresh' => 'Reset and re-run all migrations',
                    'migrate:reset' => 'Rollback all database migrations',
                    'migrate:rollback' => 'Rollback the last database migration',
                    'migrate:status' => 'Show the status of each migration',
                ],
                'route' => [
                    'route:list' => 'List all registered routes',
                ]
            ];
            echo "NovaLite Framework \033[32m1.0.0\n\n";

            echo "\033[33mUsage:\033[0m\n";
            echo "  command [options] [arguments]\n\n";

            echo "\033[33mOptions:\033[0m\n";
            foreach ($options as $option => $description) {
                echo "  \033[32m{$option}\033[0m";
                echo str_pad('', 20 - strlen($option));
                echo "{$description}\n";
            }
            echo "\n";
            echo "\033[33mAvailable commands:\033[0m\n";

            foreach ($commands as $command => $description) {
                echo "  \033[32m{$command}\033[0m";
                echo str_pad('', 20 - strlen($command));
                echo "{$description}\n";
            }
            foreach ($groupedCommands as $command => $options){
                echo "\033[33m $command\033[0m\n";
                foreach ($options as $option => $description) {
                    echo "  \033[32m{$option}\033[0m";
                    echo str_pad('', 20 - strlen($option));
                    echo "{$description}\n";
                }
            }
            exit;
        }

        if ($command === '--version') {
            echo "NovaLite Framework 1.0.0\n";
            exit;
        }
        $config = [
            'db' => Config::get('database'),
            'log' => Config::get('logging')
        ];

        $app = new Application(dirname(__DIR__, 2),$config);
        $commandClass = "NovaLite\\CLI\\Commands\\" . ucfirst($command) . "Command";
        if(str_contains($command, 'migrate:')) {
            [$migrate, $operation] = explode(':', $command);
            $commandClass = "NovaLite\\CLI\\Commands\\Migrate\\" . ucfirst($operation) . "Command";
        }
        if(str_contains($command, 'make:')) {
            [$make, $operation] = explode(':', $command);
            $commandClass = "NovaLite\\CLI\\Commands\\Make\\" . ucfirst($operation) . "Command";
        }
        if(str_contains($command, 'db:')) {
            [$db, $operation] = explode(':', $command);
            $commandClass = "NovaLite\\CLI\\Commands\\Db\\" . ucfirst($operation) . "Command";
        }
        if($command === 'route:list') {
            $commandClass = "NovaLite\\CLI\\Commands\\RouteListCommand";
        }
        if (class_exists($commandClass)) {
            $instance = new $commandClass();
            $instance->handle(array_slice($argv, 2));
        } else {
            echo "Command not found.\n";
        }
    }
}