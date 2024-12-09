<?php

namespace NovaLite\Logging;

use NovaLite\Application;

class Logger
{
    protected static string $logFile;
    public function __construct(array $logConfig)
    {
        self::$logFile = Application::$ROOT_DIR . '\\' . $logConfig['path'];
        if($logConfig['daily']){
            self::$logFile = dirname(self::$logFile) . '/app_' . date('Y-m-d') . '.log';
        }
        if(!file_exists(dirname(self::$logFile))) {
            mkdir(dirname( self::$logFile), 0777, true);
        }
    }
   public static function log($level, $message, $context = []) : void
   {
       $date = date('Y-m-d H:i:s');
       if(str_contains($message, '{')) {
           foreach ($context as $key => $value) {
               $message = str_replace('{$' . $key . '}', $value, $message);
           }
              $logMessage = "[{$date}] {$level}: {$message}" . PHP_EOL;
       }
       else{
           $contextString = json_encode($context);
           $logMessage = "[{$date}] {$level}: {$message} {$contextString}" . PHP_EOL;
       }

       file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
   }

    public static function info($message, $context = []) : void
    {
       self::log('INFO', $message, $context);
    }

    public static function error($message, $context = []) : void
    {
        self::log('ERROR', $message, $context);
    }
    public static function emergency($message, $context = []) : void
    {
        self::log('EMERGENCY', $message, $context);
    }
    public static function alert($message, $context = []) : void
    {
        self::log('ALERT', $message, $context);
    }
    public static function critical($message, $context = []) : void
    {
        self::log('CRITICAL', $message, $context);
    }
    public static function warning($message, $context = []) : void
    {
        self::log('WARNING', $message, $context);
    }
    public static function notice($message, $context = []) : void
    {
        self::log('NOTICE', $message, $context);
    }
    public static function debug($message, $context = []) : void
    {
        self::log('DEBUG', $message, $context);
    }

}