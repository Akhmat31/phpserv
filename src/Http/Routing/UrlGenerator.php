<?php

namespace Source\Http\Routing;

use InvalidArgumentException;

/**
 * Class UrlGenerator
 * Generates URLs from named routes
 */
class UrlGenerator
{
    private RouteCollection $routes;

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Generate URL for a named route
     */
    public function route(string $name, array $parameters = []): string
    {
        $route = $this->routes->getByName($name);
        
        if (!$route) {
            throw new InvalidArgumentException("Route [{$name}] not found.");
        }
        
        return $this->generate($route, $parameters);
    }

    /**
     * Generate URL from route and parameters
     */
    private function generate(Route $route, array $parameters): string
    {
        $path = $route->getPath();
        $parameterNames = $route->getParameterNames();
        
        // Replace parameters in path
        foreach ($parameterNames as $paramName) {
            if (!isset($parameters[$paramName])) {
                // Check if parameter is optional
                if (str_contains($path, "{{$paramName}?}")) {
                    $path = str_replace(["/{{$paramName}?}", "{{$paramName}?}"], '', $path);
                    continue;
                }
                
                throw new InvalidArgumentException(
                    "Missing required parameter [{$paramName}] for route [{$route->getName()}]."
                );
            }
            
            $value = $parameters[$paramName];
            
            // Validate against constraints
            $wheres = $route->getWheres();
            if (isset($wheres[$paramName])) {
                $pattern = $wheres[$paramName];
                if (!preg_match("/^{$pattern}$/", $value)) {
                    throw new InvalidArgumentException(
                        "Parameter [{$paramName}] value [{$value}] does not match constraint [{$pattern}]."
                    );
                }
            }
            
            $path = str_replace([
                "{{$paramName}}",
                "{{$paramName}?}"
            ], $value, $path);
            
            unset($parameters[$paramName]);
        }
        
        // Add remaining parameters as query string
        if (!empty($parameters)) {
            $path .= '?' . http_build_query($parameters);
        }
        
        return $path;
    }

    /**
     * Check if named route exists
     */
    public function has(string $name): bool
    {
        return $this->routes->hasNamedRoute($name);
    }
}