<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Middleware\CallableMiddlewareDecorator;

if (!function_exists('PhxPlugins\Stratigility\middleware')) {
    /**
     * Decorate a callable as a standard middleware.
     */
    function middleware(callable $middleware): CallableMiddlewareDecorator
    {
        return new CallableMiddlewareDecorator($middleware);
    }
}

