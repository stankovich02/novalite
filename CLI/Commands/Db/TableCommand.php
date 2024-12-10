<?php

namespace NovaLite\CLI\Commands\Db;

use Dotenv\Dotenv;
use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;
use NovaLite\Database\Database;

class TableCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args): void
    {
        require_once Application::$ROOT_DIR . '/vendor/autoload.php';

        $dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
        $dotenv->load();
        $this->printHelp($args,'Display information about the given database table','db:table [table_name]');
        $max = 150;
        if(!isset($args[0])){
          $tables = Database::statement('SHOW TABLES')->fetchAll();
          $key = 'Tables_in_' . $_ENV['DB_NAME'];
          $tables = array_map(fn($table) => $table->$key, $tables);
          echo "\n";
          echo " \033[1mWhich table would you like to inspect?\033[0m\n";
          foreach ($tables as $index => $table) {
              echo " $table ";
              echo str_repeat(".", $max - strlen(" $table ") - strlen($index));
              echo " $index\n";
          }
          $tableName = readline(" Enter table name: ");
          $this->printTableInfo($tableName);
        }
        else{
            $this->printTableInfo($args[0]);
        }
    }
    private function printTableInfo(string $table) : void
    {
        echo "\n";
        echo " \033[32mTable information\033[0m ";
        echo str_repeat(".", 150 - strlen(" Table information "));
        echo "\n";
        echo " Columns ";
        $numOfColumns = Database::statement("SELECT COUNT(*) AS number FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'")->fetch()->number;
        echo str_repeat(".", 150 - strlen(" Columns ") - strlen(" $numOfColumns "));
        echo " $numOfColumns\n";
        echo " Size ";
        $tableSize = Database::statement("SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $_ENV['DB_NAME'] . "' AND TABLE_NAME = '$table'")->fetch()->size;
        echo str_repeat(".", 150 - strlen(" Size ") - strlen(" $tableSize MB "));
        echo " $tableSize MB\n";
        echo "\n\n";

        echo " \033[32mColumns\033[0m ";
        echo str_repeat(".", 150 - strlen(" Columns ") - strlen(" Type "));
        echo " Type\n";
        $columns = Database::statement("SHOW COLUMNS FROM $table")->fetchAll();
        foreach ($columns as $column) {
            echo " $column->Field ";
            $null = $column->Null === 'NO' ? "" : " \033[90mnullable\033[0m ";
            echo $null;
            switch ($column->Type){
                case str_contains($column->Type,'int'):
                    $value =  "integer";
                    break;
                case str_contains($column->Type,'varchar'):
                    $value = "string";
                    break;
                default:
                    $value = "$column->Type";
                    break;
            }
            $null = $this->stripAnsiCodes($null);
            echo str_repeat(".", 150 - strlen(" $column->Field $null") - strlen(" $value"));
            echo " $value\n";
        }
        echo "\n";
    }
    private function stripAnsiCodes(string $text): string
    {
        return preg_replace('/\033\[[0-9;]*m/', '', $text);
    }
}