<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Middleware\PathMiddlewareDecorator;

if (!function_exists('PhxPlugins\Stratigility\path')) {
    /**
     * Decorate a middleware to only run for matching path prefix.
     */
    function path(string $path, mixed $middleware): PathMiddlewareDecorator
    {
        return new PathMiddlewareDecorator($path, $middleware);
    }
}

