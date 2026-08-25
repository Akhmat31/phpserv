<?php

namespace Source\Http\Routing;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

/**
 * Class RouteCollection
 * Manages a collection of routes
 */
class RouteCollection implements Countable, IteratorAggregate
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $methodRoutes = [];

    /**
     * Add a route to the collection
     */
    public function add(Route $route): void
    {
        $method = $route->getMethod();
        $path = $route->getPath();
        
        $this->routes[] = $route;
        $this->methodRoutes[$method][] = $route;
        
        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Get route by name
     */
    public function getByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Get routes by method
     */
    public function getByMethod(string $method): array
    {
        return $this->methodRoutes[strtoupper($method)] ?? [];
    }

    /**
     * Get all routes
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Get all named routes
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Check if collection has a named route
     */
    public function hasNamedRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Count routes
     */
    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * Get iterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->routes);
    }

    /**
     * Clear all routes
     */
    public function clear(): void
    {
        $this->routes = [];
        $this->namedRoutes = [];
        $this->methodRoutes = [];
    }
}