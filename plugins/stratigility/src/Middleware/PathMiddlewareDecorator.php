<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;

class PathMiddlewareDecorator
{
    private string $path;
    private mixed $middleware;

    public function __construct(string $path, mixed $middleware)
    {
        $this->path = $path;
        $this->middleware = $middleware;
    }

    /**
     * Process request if path matches; otherwise skip to next handler.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        $requestPath = '/';
        if (is_object($request) && method_exists($request, 'getPath')) {
            $requestPath = $request->getPath();
        }

        if (Utils::matchPath($requestPath, $this->path)) {
            if (is_object($this->middleware) && method_exists($this->middleware, 'process')) {
                return Utils::autoResponse($this->middleware->process($request, $handler));
            }
            if (is_callable($this->middleware)) {
                return Utils::autoResponse(($this->middleware)($request, $handler));
            }
            if (is_object($this->middleware) && method_exists($this->middleware, 'handle')) {
                return Utils::autoResponse($this->middleware->handle($request));
            }
        }

        if (is_object($handler) && method_exists($handler, 'handle')) {
            return Utils::autoResponse($handler->handle($request));
        }
        if (is_callable($handler)) {
            return Utils::autoResponse($handler($request));
        }

        return Utils::autoResponse($handler);
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

