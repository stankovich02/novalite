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
use NovaLite\Validations\ValidationError;

class View
{
    protected Factory $viewFactory;
    private array $params = [];
    private string $path = '';

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
        $this->viewFactory->share('errors', $_SESSION['errors'] ?? new ValidationError([]));
    }

    public function renderView() : string
    {
        if(str_contains($this->path, '.')){
            $this->path = str_replace('.', '/', $this->path);
        }
        return $this->viewFactory->make($this->path, $this->params)->render();
    }

    public function setParams(array $params) : void
    {
        $this->params = $params;
    }
    public function setPath(string $path) : void
    {
        $this->path = $path;
    }
}