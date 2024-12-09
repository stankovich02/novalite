<?php

namespace NovaLite\Views;

use NovaLite\Application;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class View
{
    protected Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(Application::$ROOT_DIR . '/views');

        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => Application::$ROOT_DIR . '/cache/twig',
        ]);
        $this->twig->addFunction(new \Twig\TwigFunction('route', function ($name) {
            return route($name);
        }));
        $this->twig->addFunction(new \Twig\TwigFunction('old', function ($key) {
            return old($key);
        }));
        $this->twig->addExtension(new DebugExtension());

    }

    public function renderView(string $view, array $params = []) : string
    {
        if(str_contains($view, '.')){
            $view = str_replace('.', '/', $view);
        }
        return $this->twig->render("$view.twig", $params);
    }

    public function addGlobal($key, $value) : void
    {
        $this->twig->addGlobal($key, $value);
    }
}