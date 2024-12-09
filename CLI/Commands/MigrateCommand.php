<?php

namespace NovaLite\CLI\Commands;

use NovaLite\CLI\PrintHelp;
use NovaLite\Database\Migrations\Migrator;

class MigrateCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $options = [
            '--path=PATH' => 'The path to the migrations files to be executed',
        ];
        $this->printHelp($args, 'Run the database migrations', 'migrate [options]', $options);
        $path = null;
        if(isset($args[0]) && str_contains($args[0], '--path=')){
            $path = str_replace('--path=', '', $args[0]);
        }
        Migrator::run($path);
    }
}