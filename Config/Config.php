<?php

namespace NovaLite\Config;

use NovaLite\Application;

class Config
{
    protected static array $config = [];

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);

        if(!isset(self::$config[$keys[0]])){
           $path = dirname(__DIR__,3) . "/config/{$keys[0]}.php";
           if(file_exists($path)) {
               self::$config[$keys[0]] = require $path;
           }
              else {
                return $default;
              }
        }

        $config = self::$config[$keys[0]];
        for($i = 1; $i < count($keys); $i++){
            if(isset($config[$keys[$i]])){
                $config = $config[$keys[$i]];
            }
            else {
                return $default;
            }
        }

        return $config;
    }
}