<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Middleware\HostMiddlewareDecorator;

if (!function_exists('PhxPlugins\Stratigility\host')) {
    /**
     * Decorate a middleware to only run for matching host.
     */
    function host(string $host, mixed $middleware): HostMiddlewareDecorator
    {
        return new HostMiddlewareDecorator($host, $middleware);
    }
}

