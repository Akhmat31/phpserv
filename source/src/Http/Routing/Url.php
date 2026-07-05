<?php

namespace Source\Http\Routing;

/**
 * Class Url
 * Represents a URL pattern for routing
 */
class Url
{
    private string $path;
    private string $method;

    private function __construct(string $path, string $method = 'GET')
    {
        $this->path = $path;
        $this->method = strtoupper($method);
    }

    /**
     * Create a URL pattern for any HTTP method
     */
    public static function path(string $path): self
    {
        return new self($path, 'GET');
    }

    /**
     * Create a GET URL pattern
     */
    public static function get(string $path): self
    {
        return new self($path, 'GET');
    }

    /**
     * Create a POST URL pattern
     */
    public static function post(string $path): self
    {
        return new self($path, 'POST');
    }

    /**
     * Create a PUT URL pattern
     */
    public static function put(string $path): self
    {
        return new self($path, 'PUT');
    }

    /**
     * Create a PATCH URL pattern
     */
    public static function patch(string $path): self
    {
        return new self($path, 'PATCH');
    }

    /**
     * Create a DELETE URL pattern
     */
    public static function delete(string $path): self
    {
        return new self($path, 'DELETE');
    }

    /**
     * Set the HTTP method
     */
    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Get the path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return "{$this->method} {$this->path}";
    }
}