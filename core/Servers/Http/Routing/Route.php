<?php

namespace Source\Http\Routing;

use Closure;

/**
 * Class Route
 * Represents a single route with enhanced features
 */
class Route
{
    private string $method;
    private string $path;
    private Closure|array|string $action;
    private ?string $name = null;
    private array $wheres = [];
    private array $defaults = [];
    private array $middleware = [];
    private ?string $domain = null;
    private ?string $forcedContentType = null;

    public function __construct(string $method, string $path, Closure|array|string $action)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->action = $action;
    }

    /**
     * Set route name for URL generation
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Add parameter constraint
     */
    public function where(string|array $parameter, ?string $pattern = null): self
    {
        if (is_array($parameter)) {
            $this->wheres = array_merge($this->wheres, $parameter);
        } else {
            $this->wheres[$parameter] = $pattern;
        }
        return $this;
    }

    /**
     * Add numeric constraint
     */
    public function whereNumber(string $parameter): self
    {
        return $this->where($parameter, '[0-9]+');
    }

    /**
     * Add alpha constraint
     */
    public function whereAlpha(string $parameter): self
    {
        return $this->where($parameter, '[a-zA-Z]+');
    }

    /**
     * Add alphanumeric constraint
     */
    public function whereAlphaNumeric(string $parameter): self
    {
        return $this->where($parameter, '[a-zA-Z0-9]+');
    }

    /**
     * Add UUID constraint
     */
    public function whereUuid(string $parameter): self
    {
        return $this->where($parameter, '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    }

    /**
     * Set default parameter values
     */
    public function defaults(array $defaults): self
    {
        $this->defaults = array_merge($this->defaults, $defaults);
        return $this;
    }

    /**
     * Add middleware to this route. A [path, callable] pair applies conditionally.
     */
    public function middleware(callable|array $middleware): self
    {
        if (is_callable($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            foreach ($middleware as $item) {
                if (is_callable($item) || (is_array($item) && count($item) === 2)) {
                    $this->middleware[] = $item;
                }
            }
        }
        return $this;
    }

    /**
     * Get middleware registered for this route.
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set domain constraint
     */
    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Forcing in json temporary
     */
    public function forceContentType(string $contentType): self
    {
        $this->forcedContentType = $contentType;
        return $this;
    }

    /**
     * Get Content-Type
     */
    public function getForcedContentType(): ?string
    {
        return $this->forcedContentType;
    }

    /**
     * Get route method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get route path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get route action
     */
    public function getAction(): Closure|array|string
    {
        return $this->action;
    }

    /**
     * Get parameter constraints
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * Get default values
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Get domain constraint
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Convert route path to FastRoute-compatible pattern with constraints
     */
    public function getCompiledPath(): string
    {
        $path = $this->path;

        // FastRoute supports inline patterns as {name:pattern}. Optional
        // segments use the bracket syntax [/{name}] — the leading slash must
        // be inside the brackets so the segment can be omitted entirely.
        $path = preg_replace_callback(
            '#(/?)\{(\w+)(\?)?\}#',
            function ($matches) {
                $slash = $matches[1];
                $param = $matches[2];
                $optional = isset($matches[3]);

                $pattern = $this->wheres[$param] ?? '[^/]+';

                if ($optional) {
                    return "[{$slash}{{$param}:{$pattern}}]";
                }

                return "{$slash}{{$param}:{$pattern}}";
            },
            $path
        );

        return $path;
    }

    /**
     * Get parameter names from path
     */
    public function getParameterNames(): array
    {
        preg_match_all('/\{(\w+)\??}/', $this->path, $matches);
        return $matches[1] ?? [];
    }
}
