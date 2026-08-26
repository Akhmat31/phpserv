<?php

namespace Source\API;

use Source\Http\Response\Response;
use Source\Http\Response\HttpCode;

class ApiResponse
{
    private bool $success = true;
    private mixed $data = null;
    private string $message = '';
    private ?array $error = null;
    private array $meta = [];
    private int $statusCode = HttpCode::OK;
    private array $headers = [];
    public function __construct(){}

    public static function success(mixed $data = null, string $message = '', int $statusCode = HttpCode::OK): self
    {
        $instance = new self();
        $instance->success = true;
        $instance->data = $data;
        $instance->message = $message;
        $instance->statusCode = $statusCode;
        return $instance;
    }
    public static function error(string $message, int $statusCode = HttpCode::BAD_REQUEST, mixed $errorData = null): self
    {
        $instance = new self();
        $instance->success = false;
        $instance->message = $message;
        $instance->statusCode = $statusCode;
        $instance->error = [
            'code' => $statusCode,
            'message' => $message,
        ];
        if ($errorData !== null) {
            $instance->error['details'] = $errorData;
        }
        return $instance;
    }

    /**
     * Create a validation error response (422)
     * @param array $errors Field-level validation errors
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): self
    {
        $instance = self::error($message, HttpCode::UNPROCESSABLE_ENTITY, $errors);
        return $instance;
    }
    public static function notFound(string $message = 'Resource not found'): self
    {
        return self::error($message, HttpCode::NOT_FOUND);
    }
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::error($message, HttpCode::UNAUTHORIZED);
    }
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return self::error($message, HttpCode::FORBIDDEN);
    }
    public static function tooManyRequests(string $message = 'Too many requests', ?int $retryAfter = null): self
    {
        $instance = self::error($message, HttpCode::TOO_MANY_REQUESTS);

        if ($retryAfter !== null) {
            $instance->headers['Retry-After'] = (string) $retryAfter;
        }

        return $instance;
    }

    /**
     * Add metadata to the response (pagination, totals, etc.).
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    /**
     * Add pagination metadata.
     *
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param int $total Total item count
     * @param int $lastPage Last page number
     */
    public function withPagination(int $page, int $perPage, int $total, int $lastPage): self
    {
        $this->meta['pagination'] = [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
        ];

        return $this;
    }

    /**
     * Add a custom header.
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Set the status code.
     */
    public function withStatus(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Build the response payload array.
     */
    public function toArray(): array
    {
        $payload = [
            'success' => $this->success,
        ];

        if ($this->success) {
            $payload['data'] = $this->data;
        } else {
            $payload['error'] = $this->error;
        }

        if ($this->message !== '') {
            $payload['message'] = $this->message;
        }

        if (!empty($this->meta)) {
            $payload['meta'] = $this->meta;
        }

        return $payload;
    }

    /**
     * Convert to a Source\Http\Response\Response object.
     */
    public function toResponse(): Response
    {
        $response = Response::json($this->toArray(), $this->statusCode);

        foreach ($this->headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        return $response;
    }

    /**
     * Send the response directly to the output.
     */
    public function send(): void
    {
        $this->toResponse()->send();
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Check if this is a successful response.
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }
}
