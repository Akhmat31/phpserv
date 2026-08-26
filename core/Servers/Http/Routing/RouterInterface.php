<?php

namespace Source\Http\Routing;

use Closure;

/**
 * Interface RouterInterface
 *
 * Contract for router implementations.
 * Inspired by CodeIgniter 4's RouterInterface.
 */
interface RouterInterface
{
    /**
     * Add a GET route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function get(string $path, Closure|array|string $action): Route;

    /**
     * Add a POST route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function post(string $path, Closure|array|string $action): Route;

    /**
     * Add a PUT route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function put(string $path, Closure|array|string $action): Route;

    /**
     * Add a PATCH route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function patch(string $path, Closure|array|string $action): Route;

    /**
     * Add a DELETE route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function delete(string $path, Closure|array|string $action): Route;

    /**
     * Add an OPTIONS route.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function options(string $path, Closure|array|string $action): Route;

    /**
     * Add a route matching any HTTP method.
     *
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function any(string $path, Closure|array|string $action): Route;

    /**
     * Add a route matching specific HTTP methods.
     *
     * @param array $methods List of HTTP methods
     * @param string $path URL path pattern
     * @param Closure|array|string $action Route handler
     */
    public function match(array $methods, string $path, Closure|array|string $action): Route;

    /**
     * Create a route group with shared attributes.
     *
     * @param array $attributes Group attributes (prefix, name, domain, where)
     * @param Closure $callback Callback that registers routes within the group
     */
    public function group(array $attributes, Closure $callback): void;

    /**
     * Create RESTful resource routes.
     *
     * @param string $name Resource name (URL prefix)
     * @param string $controller Controller class name
     */
    public function resource(string $name, string $controller): void;

    /**
     * Create API resource routes (without create/edit views).
     *
     * @param string $name Resource name (URL prefix)
     * @param string $controller Controller class name
     */
    public function apiResource(string $name, string $controller): void;

    /**
     * Match an incoming request to a route.
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @return array|false Route info array or false if no match
     */
    public function matchRequest(string $method, string $path): array|false;

    /**
     * Get the route collection.
     */
    public function getRoutes(): RouteCollection;

    /**
     * Generate a URL for a named route.
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     */
    public function route(string $name, array $parameters = []): string;
}
