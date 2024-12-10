<?php

namespace NovaLite\CLI\Commands\Make;

use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;

class SeederCommand implements CommandInterface
{
    use PrintHelp, PrintCard;
    public function handle($args = [], string $modelName = null): void
    {
        $this->printHelp($args, 'Create a new seeder class', 'make:seeder [seeder_name]');
        if(isset($modelName)){
            $seederName = strtolower($modelName);
            $seederName = ucfirst($seederName) . 'Seeder';
        }
        else{
            $seederName =  count($args) < 1 ? readline(" Enter seeder name: ") : $args[0];
        }
        $seederPath = 'database/seeders/' . $seederName . '.php';
        if (!is_dir('database/seeders')) {
            mkdir('database/seeders');
        }
        if (file_exists($seederPath)) {
            $this->printCard('ERROR', 'Seeder already exists.');
            return;
        }
        $seederContent = "<?php\n\nnamespace Database\Seeders;\n\nuse NovaLite\Database\Seeder;\n\nclass $seederName extends Seeder\n{\n\tpublic function run() : void\n\t{\n\t\t//\n\t}\n}\n";
        file_put_contents($seederPath, $seederContent);
        $seederPath = realpath($seederPath);
        $this->printCard('INFO', "Seeder \033[1m[$seederPath]\033[0m created successfully.");
    }
}