<?php

namespace NovaLite\CLI\Commands\Make;

use Doctrine\Inflector\InflectorFactory;
use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;

class MigrationCommand implements CommandInterface
{

    use PrintHelp;
    use PrintCard;
    public function handle($args = [], string $modelName = null): void
    {
        require_once Application::$ROOT_DIR . '/vendor/autoload.php';
        $options = [
            '--create=TABLE_NAME' => 'The table to be created',
            '--table=TABLE_NAME' => 'The table to be modified',
            '--path=PATH' => 'The location where the migration file should be created'
        ];
        $this->printHelp($args, 'Create a new migration file', 'make:migration [migration_name] [options]', $options);
        if(isset($modelName)){
            $inflector = InflectorFactory::create()->build();
            $migrationName =  $inflector->pluralize(strtolower($modelName));
        }
        else{
            $migrationName =  count($args) < 1 ? readline(" Enter migration name: ") : $args[0];
        }
        $newMigrationName = '';
        for($i=0;$i<strlen($migrationName);$i++){
            if($migrationName[$i] >= 'A' && $migrationName[$i] <= 'Z' && $i != 0){
                $newMigrationName .= '_'.strtolower($migrationName[$i]);
            }
            else if($migrationName[$i] >= 'A' && $migrationName[$i] <= 'Z' && $i === 0){
                $newMigrationName .= strtolower($migrationName[$i]);
            }
            else{
                $newMigrationName .= $migrationName[$i];
            }
        }
        if(isset($modelName)){
            $newMigrationName = 'create_' . $newMigrationName . '_table';
        }
        if((str_contains($newMigrationName, 'create') && str_contains($newMigrationName, 'table')) || (isset
                ($args[1]) && str_contains($args[1], '--create='))){
            $tableName = str_replace('create_', '', $newMigrationName);
            $tableName = str_replace('_table', '', $tableName);
            if(isset($args[1]) && str_contains($args[1], '--create=')){
                $tableName = str_replace('--create=', '', $args[1]);
            }

            $migrationContent = "<?php\n\nuse NovaLite\Database\Migrations\Migration;\nuse NovaLite\Database\Migrations\Schema;\n\nclass $newMigrationName\n{\n\tpublic function up() : void\n\t{\n\t\tSchema::create('$tableName', function (Migration \$table) {\n\t\t\t\$table->id();\n\t\t\t\$table->timestamps();\n\t\t});\n\t}\n\n\tpublic function down() : void\n\t{\n\t\tSchema::drop('$tableName');\n\t}\n}\n";
        }
        else{
           $removedTable = str_replace('_table', '', $newMigrationName);
           $lastUnderscore = strrpos($removedTable, '_');
           $tableName = substr($removedTable, $lastUnderscore + 1 , strlen($removedTable) - $lastUnderscore);
            if(isset($args[1]) && str_contains($args[1], '--table=')){
                $tableName = str_replace('--table=', '', $args[1]);
            }

            $migrationContent = "<?php\n\nuse NovaLite\Database\Migrations\Migration;\nuse NovaLite\Database\Migrations\Schema;\n\nclass $newMigrationName\n{\n\tpublic function up() : void\n\t{\n\t\tSchema::modify('$tableName', function (Migration \$table) {\n\t\t\t//\n\t\t});\n\t}\n\n\tpublic function down() : void\n\t{\n\t\tSchema::modify('$tableName', function (Migration \$table) {\n\t\t\t//\n\t\t});\n\t}\n}\n";
        }
        if(isset($args[1]) && str_contains($args[1], '--path=')){
            $folderPath = str_replace('--path=', '', $args[1]);
        }
        else{
            $folderPath = 'database/migrations';
        }
        if (!is_dir(Application::$ROOT_DIR . "\\" . $folderPath)) {
            mkdir(Application::$ROOT_DIR . "\\" . $folderPath, 0777, true);
        }
        $migrationPath = $folderPath . '/' . date('Y_m_d_His') . '_' . $newMigrationName . '.php';
        $files = scandir(Application::$ROOT_DIR . "\\" . $folderPath);
        if(!$files){
            $this->printCard('ERROR', 'Directory not found.');
            return;
        }
        foreach($files as $file){
            if($file === '.' || $file === '..') continue;
            if(str_contains($file, $newMigrationName)){
                $this->printCard('ERROR', 'Migration already exists.');
                return;
            }
        }

        file_put_contents($migrationPath, $migrationContent);
        $migrationPath = realpath($migrationPath);
        $this->printCard('INFO', "Migration \033[1m[$migrationPath]\033[0m created successfully.");

    }
}