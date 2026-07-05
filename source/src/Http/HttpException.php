<?php

namespace Source\Http;

use Source\Exception;
use Throwable;

class HttpException extends Exception
{
    public function __construct(string $message = "Internal Server Error", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function badRequest(string $message = "Bad Request"): self
    {
        return new self($message, 400);
    }

    /**
     * Create a 401 Unauthorized exception
     */
    public static function unauthorized(string $message = "Unauthorized"): self
    {
        return new self($message, 401);
    }

    /**
     * Create a 403 Forbidden exception
     */
    public static function forbidden(string $message = "Forbidden"): self
    {
        return new self($message, 403);
    }

    /**
     * Create a 404 Not Found exception
     */
    public static function notFound(string $message = "Not Found"): self
    {
        return new self($message, 404);
    }

    /**
     * Create a 405 Method Not Allowed exception
     */
    public static function methodNotAllowed(string $message = "Method Not Allowed"): self
    {
        return new self($message, 405);
    }

    /**
     * Create a 422 Unprocessable Entity exception
     */
    public static function unprocessableEntity(string $message = "Unprocessable Entity"): self
    {
        return new self($message, 422);
    }

    /**
     * Create a 429 Too Many Requests exception
     */
    public static function tooManyRequests(string $message = "Too Many Requests"): self
    {
        return new self($message, 429);
    }

    /**
     * Create a 500 Internal Server Error exception
     */
    public static function internalServerError(string $message = "Internal Server Error"): self
    {
        return new self($message, 500);
    }

    /**
     * Create a 503 Service Unavailable exception
     */
    public static function serviceUnavailable(string $message = "Service Unavailable"): self
    {
        return new self($message, 503);
    }
}