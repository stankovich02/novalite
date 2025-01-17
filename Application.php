<?php

namespace NovaLite;

use NovaLite\Database\Database;
use NovaLite\Database\Model;
use NovaLite\Http\Controller;
use NovaLite\Http\Request;
use NovaLite\Http\Response;
use NovaLite\Logging\Logger;
use NovaLite\Routing\Router;
use NovaLite\Sessions\Session;
use Novalite\Views\View;

class Application
{
    public static Application $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public \PDO $db;
    public static string $ROOT_DIR;
    public View $view;
    public Session $session;
    public Logger $logger;

    public function __construct($rootPath, array $config)
    {
        self::$app = $this;
        self::$ROOT_DIR = $rootPath;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->view = new View();
        $this->db = Database::getInstance($config['db']);
        $this->session = new Session();
        $this->logger = new Logger($config['log']);

        set_exception_handler([$this, 'handleException']);
        set_error_handler(function ($severity, $message, $file, $line) {
            $this->handleError(new \ErrorException($message, $severity, $severity, $file, $line));
        });
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function run() : void
    {
        try{
            echo $this->router->resolve();
        }
        catch(\Exception $e){
            echo $e->getMessage();
    /*        echo $this->view->renderView('_404', [
                'exception' => $e
            ]);*/
        }
    }

    public function handleException(\Throwable $e) : void
    {
        $this->logger->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString()
        ]);

/*        $this->response->setStatusCode($e->getCode());
        echo $this->view->renderView('_404', [
            'exception' => $e
        ]);*/
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