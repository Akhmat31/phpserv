<?php

namespace Source\Http;

use Source\Http\Support\ArrayType;
use Source\Http\Support\JsonModel;

/**
 * Class Request
 * Represents an HTTP request
 */
class Request
{
    private string $method;
    private string $path;
    private array $query = [];
    private array $post = [];
    private array $headers = [];
    private array $cookies = [];
    private array $files = [];
    private array $server = [];
    private array $routeParams = [];
    private ?string $body = null;

    public function __construct(
        string $method,
        string $path,
        array $query = [],
        array $post = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $body = null
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->post = $post;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->body = $body;
    }

    /**
     * Create request from PHP globals
     */
    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $query = $_GET;
        $post = $_POST;
        $cookies = $_COOKIE;
        $files = $_FILES;
        $server = $_SERVER;
        
        // Parse headers
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }

        // Get raw body
        $body = file_get_contents('php://input');

        return new self($method, $path, $query, $post, $headers, $cookies, $files, $server, $body);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get query parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get POST parameter or form field by name
     */
    public function name(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all POST data
     */
    public function post(): array
    {
        return $this->post;
    }

    /**
     * Get route parameter
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Set route parameter (used by dispatcher)
     */
    public function setRouteParam(string $key, mixed $value): void
    {
        $this->routeParams[$key] = $value;
    }

    /**
     * Get header
     */
    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get cookie
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get raw request body
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Parse JSON body
     */
    public function json(): ?JsonModel
    {
        if ($this->body === null) {
            return null;
        }

        $data = json_decode($this->body, true);
        return $data ? new JsonModel($data) : null;
    }

    /**
     * Get all input data (query + post + json)
     */
    public function all(): array
    {
        $data = array_merge($this->query, $this->post);
        
        if ($json = $this->json()) {
            $data = array_merge($data, $json->toArray());
        }

        return $data;
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}