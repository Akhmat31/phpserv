<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Exception;

use RuntimeException;

class MiddlewarePipeNextHandlerAlreadyCalledException extends RuntimeException implements ExceptionInterface
{
    public static function create(): self
    {
        return new self('The Next handler cannot be called more than once per middleware');
    }
}

