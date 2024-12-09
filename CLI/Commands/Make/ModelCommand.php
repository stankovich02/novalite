<?php

namespace NovaLite\CLI\Commands\Make;

use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;

class ModelCommand implements CommandInterface
{
    use PrintCard,PrintHelp;
    public function handle($args): void
    {
        $options = [
            '--all' => 'Generate a migration, seeder, resource controller, and form request classes for the model',
            '--controller' => 'Generate a controller class for the model',
            '--migration' => 'Generate a new migration file for the model',
            '--seed' => 'Generate a seeder class for the model',
            '--requests' => 'Generate form request classes for the model',
        ];
        $this->printHelp($args, 'Create a new model class', 'make:model [model_name] [options]', $options);
        $modelName =  count($args) < 1 ? readline(" Enter model name: ") : $args[0];
        $modelPath = 'app/Models/' . $modelName . '.php';
        if (!is_dir('app/Models')) {
            mkdir('app/Models');
        }
        if (file_exists($modelPath)) {
            $this->printCard('ERROR', 'Model already exists.');
            return;
        }
        $modelContent = "<?php\n\nnamespace App\Models;\n\nuse NovaLite\Database\Model;\n\nclass $modelName extends Model\n{\n\t//\n}\n";
        file_put_contents($modelPath, $modelContent);
        $modelPath = realpath($modelPath);
        $this->printCard('INFO', "Model \033[1m[$modelPath]\033[0m created successfully.");
        if(isset($args[1])){
            switch ($args[1]) {
                case '--all':
                    $migrationCommand = new MigrationCommand();
                    $controllerCommand = new ControllerCommand();
                    $seederCommand = new SeederCommand();
                    $requestCommand = new RequestCommand();
                    $requestCommand->handle(['Store' . $modelName . 'Request']);
                    $requestCommand->handle(['Update' . $modelName . 'Request']);
                    $seederCommand->handle(modelName: $modelName);
                    $migrationCommand->handle(modelName: $modelName);
                    $controllerCommand->handle([null,'--resource'],$modelName);
                    break;
                case '--controller':
                    $controllerCommand = new ControllerCommand();
                    $controllerCommand->handle(modelName: $modelName);
                    break;
                case '--migration':
                    $migrationCommand = new MigrationCommand();
                    $migrationCommand->handle(modelName: $modelName);
                    break;
                case '--seed':
                    $seederCommand = new SeederCommand();
                    $seederCommand->handle(modelName: $modelName);
                    break;
                case '--requests':
                    $requestCommand = new RequestCommand();
                    $requestCommand->handle(['Store' . $modelName . 'Request']);
                    $requestCommand->handle(['Update' . $modelName . 'Request']);
                    break;
                default:
                    $this->printCard('ERROR', 'Invalid option.');
                    return;
            }
        }
    }
}