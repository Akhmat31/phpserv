<?php

namespace Phxroute\Http;

use Phxroute\Http\Method\HttpMethod;

class Common
{
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    public const HEADER_ACCEPT = 'Accept';
    public const HEADER_AUTHORIZATION = 'Authorization';
    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_HTML = 'text/html';
    public const CONTENT_TYPE_XML = 'application/xml';
    public const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    public static function getSupportedMethods(): array
    {
        return [
            HttpMethod::GET,
            HttpMethod::POST,
            HttpMethod::PUT,
            HttpMethod::PATCH,
            HttpMethod::DELETE,
            HttpMethod::OPTIONS,
            HttpMethod::HEAD
        ];
    }
    public static function isValidMethod(string $method): bool
    {
        return in_array (strtoupper($method), self::getSupportedMethods());
    }
}