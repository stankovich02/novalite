<?php

namespace NovaLite\CLI\Commands;

use Dotenv\Dotenv;
use NovaLite\CLI\PrintHelp;

class AboutCommand implements CommandInterface
{
    use PrintHelp;
    public function handle($args) : void
    {
        $this->printHelp($args, 'Display basic information about your application', 'about [options]');
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
        $dotenv->load();

        $max = 100;

        $aboutInfo = [
            'Application name' => $_ENV['APP_NAME'],
            'Application version' => '1.0.0',
            'PHP version' => PHP_VERSION,
            'Composer version' => $this->getComposerVersion(),
            'Environment' => $_ENV['APP_ENV'],
            'URL' => $this->getURL(),
        ];


        echo " \033[32mEnvironment\033[0m\n";
        foreach ($aboutInfo as $key => $value) {
            echo " " . $key . " ";
            echo str_repeat(".", $max - strlen(" " . $key . " ") - strlen($value));
            echo " " . $value . "\n";
        }
    }
    private function getComposerVersion() : string
    {
        $version = shell_exec('composer -V');
        $version = explode(" ", $version);
        return $version[2];
    }
    private function getURL() : string
    {
        return explode("//", $_ENV['APP_URL'])[1];
    }
}