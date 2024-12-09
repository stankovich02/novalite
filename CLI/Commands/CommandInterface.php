<?php

namespace NovaLite\CLI\Commands;

interface CommandInterface
{
    public function handle($args) : void;
}