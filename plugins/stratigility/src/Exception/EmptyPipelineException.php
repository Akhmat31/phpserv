<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Exception;

use OutOfBoundsException;

class EmptyPipelineException extends OutOfBoundsException implements ExceptionInterface
{
    public static function forPipeline(): self
    {
        return new self('Cannot process empty pipeline without a request handler');
    }
}

