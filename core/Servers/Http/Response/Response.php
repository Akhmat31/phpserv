<?php

namespace Source\Http\Response;

/**
 * Class Response
 * Represents an HTTP response
 */
class Response
{
    private string $content;
    private int $statusCode;
    private array $headers;

    /**
     * Static registry for custom error views
     * Format: [statusCode => viewPath]
     */
    private static array $errorViews = [];

    /**
     * Register a custom error view for a specific status code
     */
    public static function setErrorView(int $statusCode, string $viewPath): void
    {
        self::$errorViews[$statusCode] = $viewPath;
    }

    /**
     * Get the custom error view path for a status code
     */
    public static function getErrorView(int $statusCode): ?string
    {
        return self::$errorViews[$statusCode] ?? null;
    }

    /**
     * Check if a custom error view exists for a status code
     */
    public static function hasErrorView(int $statusCode): bool
    {
        return isset(self::$errorViews[$statusCode]);
    }

    public function __construct(string $content = "", int $statusCode = HttpCode::OK, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Create a JSON response
     * Automatically applies custom error views for error status codes (4xx, 5xx)
     */
    public static function json(mixed $data, int $statusCode = HttpCode::OK): self
    {
        // Check for custom error view for client/server errors
        if (self::hasErrorView($statusCode)) {
            $viewPath = self::$errorViews[$statusCode];
            return self::view($viewPath, ['error' => $data, 'statusCode' => $statusCode], $statusCode);
        }

        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($content, $statusCode, ['Content-Type' => 'application/json']);
    }

    /**
     * Create an HTML response
     * Automatically applies custom error views for error status codes (4xx, 5xx)
     */
    public static function html(string $html, int $statusCode = HttpCode::OK): self
    {
        // Check for custom error view for client/server errors
        if (self::hasErrorView($statusCode)) {
            $viewPath = self::$errorViews[$statusCode];
            return self::view($viewPath, ['content' => $html, 'statusCode' => $statusCode], $statusCode);
        }

        return new self($html, $statusCode, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Create a text response
     */
    public static function text(string $text, int $statusCode = HttpCode::OK): self
    {
        return new self($text, $statusCode, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, int $statusCode = HttpCode::FOUND): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    /**
     * Create a view response by loading a view file
     * 
     * @param string $viewPath Path to the view file (relative or absolute)
     * @param array $data Data to be extracted and made available to the view
     * @param int $statusCode HTTP status code
     * @return self
     * @throws \RuntimeException If view file not found
     */
    public static function view(string $viewPath, array $data = [], int $statusCode = HttpCode::OK): self
    {
        // If path is relative, make it relative to current working directory
        if (!str_starts_with($viewPath, '/')) {
            $viewPath = getcwd() . '/' . $viewPath;
        }

        // Check if file exists
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        // Check if it's a readable file
        if (!is_file($viewPath) || !is_readable($viewPath)) {
            throw new \RuntimeException("View file is not readable: {$viewPath}");
        }

        // Extract data array to variables
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include the view file
        include $viewPath;

        // Get the buffered content
        $content = ob_get_clean();

        // Determine content type based on file extension
        $extension = strtolower(pathinfo($viewPath, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'php', 'html', 'htm' => 'text/html; charset=utf-8',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'txt' => 'text/plain; charset=utf-8',
            'css' => 'text/css',
            'js' => 'application/javascript',
            default => 'text/html; charset=utf-8'
        };

        return new self($content, $statusCode, ['Content-Type' => $contentType]);
    }

    /**
     * Set response content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set a header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Output content
        echo $this->content;
    }
}