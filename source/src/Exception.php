<?php

namespace Source;

use Exception as BaseException;
use Throwable;

/**
 * Base Exception class for PHXRoute framework
 * Provides a convenient way to use Exception without the backslash
 */
class Exception extends BaseException
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param Throwable|null $previous Previous throwable
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}