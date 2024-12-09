<?php

namespace NovaLite\CLI\Commands\Migrate;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Database\Database;
use NovaLite\Database\Migrations\Migrator;

class FreshCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Drop all tables and re-run all migrations', 'migrate:fresh [options]');
        Migrator::dropAllTables();
        Migrator::run();
    }
}