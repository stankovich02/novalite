<?php

namespace NovaLite;

use NovaLite\Database\Database;
use NovaLite\Database\Model;
use NovaLite\Http\Controller;
use NovaLite\Http\RedirectResponse;
use NovaLite\Http\Request;
use NovaLite\Http\Response;
use NovaLite\Logging\Logger;
use NovaLite\Routing\Router;
use NovaLite\Sessions\Session;
use NovaLite\Views\View;

class Application
{
    public static Application $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public \PDO $db;
    public static string $ROOT_DIR;
    public static View $view;
    public Session $session;
    public Logger $logger;

    public function __construct($rootPath, array $config)
    {
        self::$app = $this;
        self::$ROOT_DIR = $rootPath;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = Database::getInstance($config['db']);
        $this->session = new Session();
        $this->logger = new Logger($config['log']);
        self::$view = new View(self::$ROOT_DIR . '/views', self::$ROOT_DIR . '/cache/views');
        set_exception_handler([$this, 'handleException']);
        set_error_handler(function ($severity, $message, $file, $line) {
            $this->handleError(new \ErrorException($message, $severity, $severity, $file, $line));
        });
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function run() : void
    {
        try{
            $response = $this->router->resolve();

            if ($response instanceof RedirectResponse) {
                $response->send();
                return;
            }

            echo $response;
        }
        catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    public function handleException(\Throwable $e) : void
    {
        $this->logger->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString()
        ]);
    }
    public function handleError(\Throwable $e) : void
    {
        $this->logger->error($e->getMessage(), [
            'severity' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    public function handleFatalError() : void
    {
        $error = error_get_last();
        if($error !== null){
            $this->logger->error('Fatal Error', [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
            ]);
        }
    }
}