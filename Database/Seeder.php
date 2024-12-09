<?php

namespace NovaLite\Database;

abstract class Seeder
{
    abstract public function run(): void;

    public function call(array $seeders) : void
    {
        foreach ($seeders as $seeder) {
            $seeder = new $seeder();
            $seeder->run();
        }
    }
}