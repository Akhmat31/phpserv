<?php

declare(strict_types=1);

use PhxPlugins\Stratigility\Pipeline;
use PhxPlugins\Stratigility\Utils;
use Source\Http\Request;
use Source\Http\Response\Response;

require_once __DIR__ . '/../plugins/stratigility/src/helpers/double-pass.php';
require_once __DIR__ . '/../plugins/stratigility/src/helpers/host.php';
require_once __DIR__ . '/../plugins/stratigility/src/helpers/middleware.php';
require_once __DIR__ . '/../plugins/stratigility/src/helpers/path.php';

if (!function_exists('middleware_pipe')) {
    /**
     * Create a middleware pipeline array or normalize an existing list.
     *
     * @param array $middleware
     * @return array
     */
    function middleware_pipe(array $middleware = []): array
    {
        return array_values($middleware);
    }
}

if (!function_exists('middleware_add')) {
    /**
     * Add a middleware callable to the pipeline array.
     */
    function middleware_add(array &$pipeline, callable $middleware): void
    {
        $pipeline[] = $middleware;
    }
}

if (!function_exists('middleware_run')) {
    /**
     * Run a request through the given middleware pipeline ending with the handler.
     */
    function middleware_run(Request $request, array $middleware, callable $handler): Response
    {
        $pipeline = new Pipeline($middleware);
        return $pipeline->process($request, $handler);
    }
}

if (!function_exists('middleware_response')) {
    /**
     * Convert any result into a standard Response instance.
     */
    function middleware_response(mixed $result): Response
    {
        return Utils::autoResponse($result);
    }
}

if (!function_exists('middleware_matches')) {
    /**
     * Check if a request path matches a path pattern.
     */
    function middleware_matches(string $requestPath, string $pattern): bool
    {
        return Utils::matchPath($requestPath, $pattern);
    }
}

if (!function_exists('middleware_for_route')) {
    /**
     * Combine global and route-level middleware for a given request path.
     *
     * @param array $global
     * @param array $routeMiddleware
     * @param string $path
     * @return callable[]
     */
    function middleware_for_route(array $global, array $routeMiddleware, string $path): array
    {
        $resolved = [];

        foreach ($global as $middleware) {
            $resolved[] = $middleware;
        }

        foreach ($routeMiddleware as $middleware) {
            $resolved[] = $middleware;
        }

        return $resolved;
    }
}

return static function (array $middleware = []): array {
    return middleware_pipe($middleware);
};