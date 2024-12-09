<?php

namespace NovaLite\Database;

use Doctrine\Inflector\InflectorFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

trait TableGuess
{

    protected function guessTableName($instance): string
    {
        if (!empty($instance->table)) {
            return $instance->table;
        }

        $className = (new \ReflectionClass($instance))->getShortName();

        $inflector = InflectorFactory::create()->build();
        return $inflector->pluralize(strtolower($className));
    }
}