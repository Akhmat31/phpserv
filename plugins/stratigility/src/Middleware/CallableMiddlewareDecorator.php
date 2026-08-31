<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;

class CallableMiddlewareDecorator
{
    /** @var callable */
    private $middleware;

    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Process incoming request by delegating to the wrapped callable.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        $result = ($this->middleware)($request, $handler);
        return Utils::autoResponse($result);
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

