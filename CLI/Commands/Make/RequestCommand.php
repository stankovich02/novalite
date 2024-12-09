<?php

namespace NovaLite\CLI\Commands\Make;

use NovaLite\CLI\Commands\CommandInterface;
use NovaLite\CLI\PrintCard;
use NovaLite\CLI\PrintHelp;

class RequestCommand implements CommandInterface
{
    use PrintHelp, PrintCard;
    public function handle($args): void
    {
        $this->printHelp($args, 'Create a new request class', 'make:request [request_name]');
        $requestName =  count($args) < 1 ? readline(" Enter request name: ") : $args[0];
        $requestPath = 'app/Requests/' . $requestName . '.php';
        if (!is_dir('app/Requests')) {
            mkdir('app/Requests');
        }
        if (file_exists($requestPath)) {
            $this->printCard('ERROR', 'Request already exists.');
            return;
        }
        $requestContent = "<?php\n\nnamespace App\Requests;\n\nuse NovaLite\Validations\FormRequest;\n\nclass $requestName extends FormRequest\n{\n\tprotected function rules() : array\n\t{\n\t\treturn [\n\t\t\t//\n\t\t];\n\t}\n}\n";
        file_put_contents($requestPath, $requestContent);
        $requestPath = realpath($requestPath);
        $this->printCard('INFO', "Request \033[1m[$requestPath]\033[0m created successfully.");
    }
}