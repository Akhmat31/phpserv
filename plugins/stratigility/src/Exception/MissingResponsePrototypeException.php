<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Exception;

use RuntimeException;

class MissingResponsePrototypeException extends RuntimeException implements ExceptionInterface
{
    public static function create(): self
    {
        return new self('Double pass middleware requires a response prototype');
    }
}

