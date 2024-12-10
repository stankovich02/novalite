<?php

namespace NovaLite\CLI\Commands;

use Dotenv\Dotenv;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;

class EnvCommand implements CommandInterface
{
    use PrintCard,PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Display the current framework environment', 'env [options]');
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
        $dotenv->load();


        $this->printCard('INFO',  "The application environment is \033[1m[" .  $_ENV['APP_ENV'] . "]\033[0m");
    }

}