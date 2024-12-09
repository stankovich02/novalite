<?php

namespace NovaLite\CLI;

trait PrintCard
{
    public function printCard(string $type, string $message): void
    {
        $colors = [
            'INFO' => "\033[44;97m",
            'ERROR' => "\033[41;97m",
            'SUCCESS' => "\033[42;97m",
        ];

        $reset = "\033[0m";
        $color = $colors[$type] ?? "\033[40;97m";


        echo PHP_EOL . " " . $color . " " . strtoupper($type) . " " . $reset . " " . $message . PHP_EOL .  PHP_EOL;
    }
}