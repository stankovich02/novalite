<?php

namespace NovaLite\CLI\Commands\Make;

use NovaLite\Application;
use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;
use NovaLite\Config\Config;

class ControllerCommand implements CommandInterface
{
    use PrintHelp;
    use PrintCard;
    public function handle($args = [],string $modelName = null): void
    {
        $options = [
            '--api' => 'Generate a new API controller class',
            '--resource' => 'Generate a resource controller class',
        ];
        $this->printHelp($args, 'Create a new controller class', 'make:controller [controller_name] [options]',
            $options);
        if(isset($modelName)){
            $controllerName = strtolower($modelName);
            $controllerName = ucfirst($controllerName) . 'Controller';
        }
        else{
            $controllerName =  count($args) < 1 ? readline(" Enter controller name: ") : $args[0];
        }
        $controllerPath = 'app/Controllers/' . $controllerName . '.php';
        if (!is_dir('app/Controllers')) {
            mkdir('app/Controllers');
        }
        $allFolders = [];
        if(str_contains($controllerName, '/') !== false){
            $folders = explode('/', $controllerName);
            $folderPath = 'app/Controllers';
            for($i = 0; $i < count($folders) - 1; $i++){
                $allFolders[] = $folders[$i];
                $folderPath .= '/' . $folders[$i];
                if (!is_dir($folderPath)) {
                    mkdir($folderPath);
                }
            }
        }
        if (file_exists($controllerPath)) {
            $this->printCard('ERROR', 'Controller already exists.');
            return;
        }
        if(str_contains($controllerName, '/') !== false) {
            $controllerName = explode('/', $controllerName);
            $controllerName = end($controllerName);
        }
        $namespace = str_contains($controllerName, '/') !== false ? 'App\Controllers\\' . implode('\\', $allFolders)
            . ';' : 'App\Controllers;';
        if(isset($args[1])){
            $controllerContent = match ($args[1]) {
                '--api' => "<?php\n\nnamespace $namespace\n\nuse core\Http\Controller;\nuse core\Http\Request;\n\nclass $controllerName extends Controller\n{\n\tpublic function index()\n\t{\n\t\t//\n\t}\n\n\tpublic function store(Request \$request)\n\t{\n\t\t//\n\t}\n\n\tpublic function show(string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function update(Request \$request, string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function destroy(string \$id)\n\t{\n\t\t//\n\t}\n}\n",
                '--resource' => $this->getResourceControllerContent($controllerName,$modelName),
            };
        }
        else{
            $controllerContent = "<?php\n\nnamespace $namespace\n\nuse core\Http\Controller;\n\nclass $controllerName extends Controller\n{\n    //\n}\n";
        }
        file_put_contents($controllerPath, $controllerContent);
        $controllerPath = realpath($controllerPath);
        $this->printCard('INFO', "Controller \033[1m[$controllerPath]\033[0m created successfully.");
    }
    private function getResourceControllerContent($controllerName, $modelName) : string
    {
        if(isset($modelName)){
            return "<?php\n\nnamespace App\Controllers;\n\nuse NovaLite\Http\Controller;\nuse App\Requests\Store" .
                $modelName . "Request;\nuse App\Requests\Update" .
                $modelName . "Request;\n\nclass $controllerName extends Controller\n{\n\tpublic function index()\n\t{\n\t\t//\n\t}\n\n\tpublic function create()\n\t{\n\t\t//\n\t}\n\n\tpublic function store(Store" . $modelName . "Request \$request)\n\t{\n\t\t//\n\t}\n\n\tpublic function show(string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function edit(string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function update(Update"  . $modelName . "Request \$request, string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function destroy(string \$id)\n\t{\n\t\t//\n\t}\n}\n";
        }
        return "<?php\n\nnamespace App\Controllers;\n\nuse NovaLite\Http\Controller;\nuse NovaLite\Http\Request;\n\nclass $controllerName extends Controller\n{\n\tpublic function index()\n\t{\n\t\t//\n\t}\n\n\tpublic function create()\n\t{\n\t\t//\n\t}\n\n\tpublic function store(Request \$request)\n\t{\n\t\t//\n\t}\n\n\tpublic function show(string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function edit(string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function update(Request \$request, string \$id)\n\t{\n\t\t//\n\t}\n\n\tpublic function destroy(string \$id)\n\t{\n\t\t//\n\t}\n}\n";
    }
}