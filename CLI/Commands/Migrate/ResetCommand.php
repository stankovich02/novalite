<?php

namespace NovaLite\CLI\Commands\Migrate;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Database\Migrations\Migrator;

class ResetCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Rollback all database migrations', 'migrate:reset [options]');
        Migrator::rollback(true);
    }
}