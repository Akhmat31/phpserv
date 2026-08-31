<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Middleware\DoublePassMiddlewareDecorator;
use Source\Http\Response\Response;

if (!function_exists('PhxPlugins\Stratigility\doublePassMiddleware')) {
    /**
     * Decorate a double-pass middleware callable fn($request, $response, $next).
     */
    function doublePassMiddleware(callable $middleware, ?Response $responsePrototype = null): DoublePassMiddlewareDecorator
    {
        return new DoublePassMiddlewareDecorator($middleware, $responsePrototype);
    }
}

