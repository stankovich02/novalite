<?php

namespace NovaLite\CLI\Commands\Db;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;

class SeedCommand implements CommandInterface
{
    use PrintHelp,PrintCard;
    public function handle($args): void
    {
        $options = [
            '--class=CLASS_NAME' => "The class name of the root seeder \033[33m[default: 'Database\Seeders\DatabaseSeeder']\033[0m",
        ];
        $this->printHelp($args, 'Seed the database with records', 'db:seed [options]', $options);
        $seeder = isset($args[0]) ? explode('=', $args[0])[1] : 'DatabaseSeeder';
        if (!file_exists(dirname(__DIR__, 4) . '/database/seeders/' . $seeder . '.php')) {
            $this->printCard('ERROR', 'Seeder class not found.');
            return;
        }
        $seeder = 'Database\\Seeders\\' . $seeder;
        $seeder = new $seeder();
        $seeder->run();
        $this->printCard('SUCCESS', 'Database seeded successfully.');
    }
}