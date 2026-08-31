<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Exception;

use RuntimeException;

class MissingResponseException extends RuntimeException implements ExceptionInterface
{
    public static function forMiddleware(string $middleware): self
    {
        return new self(sprintf('Middleware "%s" returned no response, expected a Response object', $middleware));
    }
}

