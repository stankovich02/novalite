<?php

namespace NovaLite\Views;

use NovaLite\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Factory;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Events\Dispatcher;

class View
{
    protected Factory $viewFactory;

    public function __construct($viewsPath, $cachePath)
    {
        $filesystem = new Filesystem();
        $resolver = new EngineResolver();

        $resolver->register('php', function () {
            return new PhpEngine();
        });

        $resolver->register('blade', function () use ($filesystem, $cachePath) {
            return new CompilerEngine(new BladeCompiler($filesystem, $cachePath));
        });

        $finder = new FileViewFinder($filesystem, [$viewsPath]);
        $dispatcher = new Dispatcher();

        $this->viewFactory = new Factory($resolver, $finder, $dispatcher);

        $this->viewFactory->share('errors', $_SESSION['errors']);
    }

    public function renderView($view, $data = []) : string
    {
        if(str_contains($view, '.')){
            $view = str_replace('.', '/', $view);
        }
        return $this->viewFactory->make($view, $data)->render();
    }
}