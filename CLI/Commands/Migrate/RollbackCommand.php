<?php

namespace NovaLite\CLI\Commands\Migrate;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Database\Migrations\Migrator;

class RollbackCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Rollback the last database migration', 'migrate:rollback [options]');
        Migrator::rollback();
    }
}