<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;

class RequestHandlerMiddleware
{
    private mixed $handler;

    public function __construct(mixed $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Treat request handler as a terminal middleware.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        if (is_object($this->handler) && method_exists($this->handler, 'handle')) {
            return Utils::autoResponse($this->handler->handle($request));
        }

        if (is_callable($this->handler)) {
            return Utils::autoResponse(($this->handler)($request));
        }

        return Utils::autoResponse($this->handler);
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

