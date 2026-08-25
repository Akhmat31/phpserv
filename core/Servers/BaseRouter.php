<?php

namespace Source;

use Source\Http\Routing\Router;
use Source\Http\Request;
use DI\Container;

class BaseRouter
{
    private Router $router;
    private Dispatcher $dispatcher;

    public function __construct(?Container $container = null)
    {
        $this->router = new Router();
        $this->dispatcher = new Dispatcher($container);
    }
    public function router(): Router
    {
        return $this->router;
    }
    public function dispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }
    public function set404(string $path): self
    {
        $this->dispatcher->set404($path);
        return $this;
    }
    public function setUnauthorized(string $path): self {
        $this->dispatcher->setUnauthorized($path);
        return $this;
    }
    public function run(): void
    {
        $request = Request::createFromGlobals();
        $response = $this->dispatcher->dispatch(
            $this->router,
            $request
        );
        $response->send();
    }
    public function route(string $name, array $parameters = []): string
    {
        return $this->router->route($name, $parameters);
    }
}