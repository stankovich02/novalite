<?php

namespace NovaLite\CLI\Commands\Migrate;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Database\Migrations\Migrator;

class RefreshCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Reset and re-run all migrations', 'migrate:refresh [options]');
        Migrator::rollback(true);
        Migrator::run();
    }
}