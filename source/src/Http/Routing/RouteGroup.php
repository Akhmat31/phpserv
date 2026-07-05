<?php

namespace Source\Http\Routing;

use Closure;

/**
 * Class RouteGroup
 * Handles route grouping with shared attributes
 */
class RouteGroup
{
    private array $attributes = [];
    private Closure $callback;
    private Router $router;

    public function __construct(Router $router, array $attributes, Closure $callback)
    {
        $this->router = $router;
        $this->attributes = $attributes;
        $this->callback = $callback;
    }

    /**
     * Execute the group callback
     */
    public function register(): void
    {
        // Store current group attributes
        $previousAttributes = $this->router->getGroupAttributes();
        
        // Merge with new attributes
        $this->router->setGroupAttributes(
            $this->mergeAttributes($previousAttributes, $this->attributes)
        );
        
        // Execute callback
        call_user_func($this->callback, $this->router);
        
        // Restore previous attributes
        $this->router->setGroupAttributes($previousAttributes);
    }

    /**
     * Merge group attributes
     */
    private function mergeAttributes(array $old, array $new): array
    {
        $merged = $new;
        
        // Merge prefixes
        if (isset($old['prefix']) || isset($new['prefix'])) {
            $merged['prefix'] = trim(($old['prefix'] ?? '') . '/' . ($new['prefix'] ?? ''), '/');
        }
        
        // Merge name prefixes
        if (isset($old['name']) || isset($new['name'])) {
            $merged['name'] = ($old['name'] ?? '') . ($new['name'] ?? '');
        }
        
        // Merge domains
        if (isset($old['domain'])) {
            $merged['domain'] = $old['domain'];
        }
        
        // Merge wheres
        if (isset($old['where']) || isset($new['where'])) {
            $merged['where'] = array_merge($old['where'] ?? [], $new['where'] ?? []);
        }
        
        return $merged;
    }
}