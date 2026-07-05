<?php

namespace Source\Http\Routing;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use Closure;
use Source\Http\Method\HttpMethod;
use function FastRoute\simpleDispatcher;

/**
 * Class Router
 * Enhanced routing class with Laravel-like features
 */
class Router
{
    private RouteCollection $routes;
    private ?Dispatcher $dispatcher = null;
    private array $groupAttributes = [];
    private UrlGenerator $urlGenerator;

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->urlGenerator = new UrlGenerator($this->routes);
    }

    /**
     * Create a route group
     */
    public function group(array $attributes, Closure $callback): void
    {
        $group = new RouteGroup($this, $attributes, $callback);
        $group->register();
    }

    /**
     * Add a GET route
     */
    public function get(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::GET, $path, $action);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::POST, $path, $action);
    }

    /**
     * Add a PUT route
     */
    public function put(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::PUT, $path, $action);
    }

    /**
     * Add a PATCH route
     */
    public function patch(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::PATCH, $path, $action);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::DELETE, $path, $action);
    }

    /**
     * Add an OPTIONS route
     */
    public function options(string $path, Closure|array|string $action): Route
    {
        return $this->addRoute(HttpMethod::OPTIONS, $path, $action);
    }

    /**
     * Add a route for any method
     */
    public function any(string $path, Closure|array|string $action): Route
    {
        $methods = [
            HttpMethod::GET, 
            HttpMethod::POST, 
            HttpMethod::PUT, 
            HttpMethod::PATCH, 
            HttpMethod::DELETE,
            HttpMethod::OPTIONS, 
            HttpMethod::HEAD
        ];
        
        $route = null;
        foreach ($methods as $method) {
            $route = $this->addRoute($method, $path, $action);
        }
        
        return $route;
    }

    /**
     * Add multiple routes for specific methods
     */
    public function match(array $methods, string $path, Closure|array|string $action): Route
    {
        $route = null;
        foreach ($methods as $method) {
            $route = $this->addRoute($method, $path, $action);
        }
        
        return $route;
    }

    /**
     * Create RESTful resource routes
     */
    public function resource(string $name, string $controller): void
    {
        $base = '/' . trim($name, '/');
        
        // Index - GET /resource
        $this->get($base, [$controller, 'index'])->name("{$name}.index");
        
        // Create - GET /resource/create
        $this->get("{$base}/create", [$controller, 'create'])->name("{$name}.create");
        
        // Store - POST /resource
        $this->post($base, [$controller, 'store'])->name("{$name}.store");
        
        // Show - GET /resource/{id}
        $this->get("{$base}/{id}", [$controller, 'show'])
            ->name("{$name}.show")
            ->whereNumber('id');
        
        // Edit - GET /resource/{id}/edit
        $this->get("{$base}/{id}/edit", [$controller, 'edit'])
            ->name("{$name}.edit")
            ->whereNumber('id');
        
        // Update - PUT/PATCH /resource/{id}
        $this->match(['PUT', 'PATCH'], "{$base}/{id}", [$controller, 'update'])
            ->name("{$name}.update")
            ->whereNumber('id');
        
        // Destroy - DELETE /resource/{id}
        $this->delete("{$base}/{id}", [$controller, 'destroy'])
            ->name("{$name}.destroy")
            ->whereNumber('id');
    }

    /**
     * Create API resource routes (without create/edit)
     */
    public function apiResource(string $name, string $controller): void
    {
        $base = '/' . trim($name, '/');
        
        $this->get($base, [$controller, 'index'])->name("{$name}.index");
        $this->post($base, [$controller, 'store'])->name("{$name}.store");
        $this->get("{$base}/{id}", [$controller, 'show'])
            ->name("{$name}.show")
            ->whereNumber('id');
        $this->match(['PUT', 'PATCH'], "{$base}/{id}", [$controller, 'update'])
            ->name("{$name}.update")
            ->whereNumber('id');
        $this->delete("{$base}/{id}", [$controller, 'destroy'])
            ->name("{$name}.destroy")
            ->whereNumber('id');
    }

    /**
     * Add a route with group attributes applied
     */
    private function addRoute(string $method, string $path, Closure|array|string $action): Route
    {
        // Apply group prefix
        if (isset($this->groupAttributes['prefix'])) {
            $path = '/' . trim($this->groupAttributes['prefix'], '/') . '/' . trim($path, '/');
        }
        
        $path = '/' . trim($path, '/');
        
        $route = new Route($method, $path, $action);
        
        // Apply group name prefix
        if (isset($this->groupAttributes['name'])) {
            // Name will be set by the caller with ->name()
            // We just store the prefix for later use
            $route->namePrefix = $this->groupAttributes['name'];
        }
        
        // Apply group domain
        if (isset($this->groupAttributes['domain'])) {
            $route->domain($this->groupAttributes['domain']);
        }
        
        // Apply group where constraints
        if (isset($this->groupAttributes['where'])) {
            $route->where($this->groupAttributes['where']);
        }
        
        $this->routes->add($route);
        
        // Reset dispatcher to force rebuild
        $this->dispatcher = null;
        
        return $route;
    }

    /**
     * Get/Set group attributes (used by RouteGroup)
     */
    public function getGroupAttributes(): array
    {
        return $this->groupAttributes;
    }

    public function setGroupAttributes(array $attributes): void
    {
        $this->groupAttributes = $attributes;
    }

    /**
     * Get the FastRoute dispatcher
     */
    private function getDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null) {
            $routes = $this->routes->all();
            
            $this->dispatcher = simpleDispatcher(function(RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    $compiledPath = $route->getCompiledPath();
                    $r->addRoute($route->getMethod(), $compiledPath, $route);
                }
            });
        }

        return $this->dispatcher;
    }

    /**
     * Match an incoming request to a route (used internally by Dispatcher)
     */
    public function matchRequest(string $method, string $path): array|false
    {
        $dispatcher = $this->getDispatcher();
        $routeInfo = $dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $route = $routeInfo[1];
                $vars = $routeInfo[2];
                
                // Apply default values for missing optional parameters
                $defaults = $route->getDefaults();
                foreach ($defaults as $key => $value) {
                    if (!isset($vars[$key])) {
                        $vars[$key] = $value;
                    }
                }
                
                return [
                    'route' => $route,
                    'handler' => $route->getAction(),
                    'vars' => $vars
                ];
            
            case Dispatcher::NOT_FOUND:
            case Dispatcher::METHOD_NOT_ALLOWED:
            default:
                return false;
        }
    }

    /**
     * Get route collection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * Get URL generator
     */
    public function url(): UrlGenerator
    {
        return $this->urlGenerator;
    }

    /**
     * Generate URL for named route
     */
    public function route(string $name, array $parameters = []): string
    {
        return $this->urlGenerator->route($name, $parameters);
    }
}