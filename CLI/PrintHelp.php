<?php

namespace NovaLite\CLI;

trait PrintHelp
{
    public function printHelp($args,string $description, string $usage, array $options = []): void
    {
        if(isset($args[0]) && $args[0] === '--help') {
            echo "\033[33mDescription:\033[0m\n";
            echo "  " . $description . "\n\n";
            echo "\033[33mUsage:\033[0m\n";
            echo "  " . $usage ."\n\n";
            echo "\033[33mOptions:\033[0m\n";
            if(!empty($options)) {
                foreach ($options as $option => $description) {
                    echo "  \033[32m{$option}\033[0m";
                    echo str_pad('', 30 - strlen($option));
                    echo "{$description}\n";
                }
            } else
            echo "  No options available.\n";
            exit(0);
        }
    }
}