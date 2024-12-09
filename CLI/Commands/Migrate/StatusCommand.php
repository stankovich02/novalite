<?php

namespace NovaLite\CLI\Commands\Migrate;

use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;
use NovaLite\Database\Database;

class StatusCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        $this->printHelp($args, 'Show the status of each migration', 'migrate:status [options]');
        $max = 100;

        echo " Migration name ";
        echo str_repeat(".", $max - strlen(" Migration name ") - strlen('Batch / Status'));
        echo " Batch / Status\n";
        $statement = Application::$app->db->prepare("SELECT migration FROM migrations");
        $statement->execute();
        $appliedMigrations = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $files = scandir(Application::$ROOT_DIR.'/database/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);
        foreach ($files as $file){
            if($file === '.' || $file === '..'){
                continue;
            }
            $className = pathinfo($file, PATHINFO_BASENAME);
            $status = in_array($className, $appliedMigrations) ? "\033[32mApplied\033[0m" : "\033[33mNot applied\033[0m";
            echo " $className ";
            echo str_repeat(".", $max - strlen(" $className ") - strlen($status));
            echo " $status\n";
        }
    }
}