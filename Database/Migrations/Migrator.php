<?php

namespace NovaLite\Database\Migrations;

use NovaLite\Application;
use NovaLite\CLI\PrintCard;
use NovaLite\Config\Config;
use NovaLite\Database\Database;

class Migrator
{
    use PrintCard;
    private int $max = 100;
    private static bool $isDroppedAll = false;
    public static function run(string $path = null) : void
    {
        if(!self::$isDroppedAll){
            $config = [
                'db' => Config::get('database'),
                'log' => Config::get('logging')
            ];

            $app = new Application(dirname(__DIR__, 3),$config);
        }
        self::applyMigrations($path);
    }
    public static function applyMigrations(string $path = null) : void
    {
        $instance = new self();
        self::createMigrationsTable();
        $appliedMigrations = self::getAppliedMigrations();
        $newMigrations = [];

        $files = $path ? scandir(Application::$ROOT_DIR . '/' . $path) : scandir(Application::$ROOT_DIR . '/database/migrations');

        $toApplyMigrations = array_diff($files, $appliedMigrations);
        if(count($toApplyMigrations) === 2){
            $instance->printCard('SUCCESS', 'All migrations are already applied.');
            return;
        }
        $instance->printCard('INFO', 'Running migrations.');
        foreach ($toApplyMigrations as $index => $migration) {
            if($migration == '.' || $migration == '..') continue;

            require_once(Application::$ROOT_DIR . '/database/migrations/' . $migration);
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $migrationInstance = new $className();
            $startTime = microtime(true);
            $migrationInstance->up();
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            $value = round($executionTime, 2) . "ms \033[32mDONE\033[0m";
            echo " $migration ";
            echo str_repeat(".", $instance->max - strlen(" $migration ") - strlen($value));
            echo " $value" . PHP_EOL;
            $newMigrations[] = $migration;
        }

        if(!empty($newMigrations)) {
            self::saveMigrations($newMigrations);
        }
        else{
            $instance->printCard('SUCCESS', 'All migrations are already applied.');
        }
    }
    private static function createMigrationsTable() : void
    {
        $instance = new self();
        if(env('DB_CONNECTION') === 'pgsql'){
            $exists = Application::$app->db->query("SELECT to_regclass('public.migrations')")->fetchColumn();
        }
        else{
            $exists = Application::$app->db->query("SHOW TABLES LIKE 'migrations'")->rowCount() > 0;
        }
        if(!$exists){
            $instance->printCard('INFO', 'Preparing database.');
        }
        $startTime = microtime(true);
        if(env('DB_CONNECTION') === 'pgsql'){
            Application::$app->db->exec(
                "
                CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );"
            );
        }
        else {
            Application::$app->db->exec(
                "
            CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;"
            );
        }
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        if(!$exists){
            $value = round($executionTime, 2) . "ms \033[32mDONE\033[0m";
            echo " Creating migrations table ";
            echo str_repeat(".", $instance->max - strlen(" Creating migrations table ") - strlen($value));
            echo " $value" . PHP_EOL;
        }
    }

    private static function getAppliedMigrations() : array
    {
        $statement = Application::$app->db->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
    public static function dropAllTables() : void
    {
        $config = [
            'db' => Config::get('database'),
            'log' => Config::get('logging')
        ];

        $app = new Application(dirname(__DIR__, 3),$config);
        self::$isDroppedAll = true;
        $instance = new self();
        $tables = Application::$app->db->query("SHOW TABLES")->fetchAll();
        $tables = array_reverse($tables);
        $startTime = microtime(true);
        foreach ($tables as $table) {
            foreach ($table as $tableName) {
                Application::$app->db->exec("DROP TABLE $tableName");
            }
        }
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        $value = round($executionTime, 2) . "ms \033[32mDONE\033[0m";
        echo PHP_EOL . " Dropping all tables ";
        echo str_repeat(".", $instance->max - strlen(" Dropping all tables ") - strlen($value));
        echo " $value" . PHP_EOL;
    }
    public static function rollback(bool $all = null) : void
    {
        $config = [
            'db' => Config::get('database'),
            'log' => Config::get('logging')
        ];

        $app = new Application(dirname(__DIR__, 3),$config);
        $instance = new self();
        if(!$all){
            $lastInsertedMigrationDate = Database::select("SELECT created_at FROM migrations ORDER BY id DESC LIMIT 1")[0]->created_at;
            $migrations = Database::select("SELECT migration FROM migrations WHERE created_at = '$lastInsertedMigrationDate'");
        }
        else{
            $migrations = Database::select("SELECT migration FROM migrations");
        }
        $migrations = array_reverse($migrations);
        if(count($migrations) === 0){
            $instance->printCard('INFO', 'No migrations to rollback.');
            return;
        }
        $instance->printCard('INFO', 'Rolling back migrations.');
        $migrationsString = implode("','", array_map(fn($m) => $m->migration, $migrations));
        foreach ($migrations as $migration) {
            require_once(Application::$ROOT_DIR . '/database/migrations/' . $migration->migration);
            $className = pathinfo($migration->migration, PATHINFO_FILENAME);
            $migrationInstance = new $className();
            $startTime = microtime(true);
            $migrationInstance->down();
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            $value = round($executionTime, 2) . "ms \033[32mDONE\033[0m";
            echo " $migration->migration ";
            echo str_repeat(".", $instance->max - strlen(" $migration->migration ") - strlen($value));
            echo " $value" . PHP_EOL;
        }
        Database::delete("DELETE FROM migrations WHERE migration IN ('$migrationsString')");
    }
    private static function saveMigrations(array $migrations) : void
    {
        $str = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = Application::$app->db->prepare("INSERT INTO migrations (migration) VALUES $str");
        $statement->execute();
    }

    private static function log(string $message) : void
    {
        echo '['.date("Y-m-d H:i:s").'] '.$message."\n";
    }
}