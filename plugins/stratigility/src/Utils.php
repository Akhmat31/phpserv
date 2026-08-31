<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use Source\Http\Response\Response;
use Source\Http\Response\HttpCode;
use JsonSerializable;

class Utils
{
    /**
     * Convert any handler/middleware output to a standard Response object.
     */
    public static function autoResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            if (self::isHtml($result)) {
                return Response::html($result);
            }
            return Response::text($result);
        }

        if (is_array($result) || $result instanceof JsonSerializable) {
            return Response::json($result);
        }

        if (is_object($result)) {
            if (method_exists($result, '__toString')) {
                $str = (string) $result;
                return self::isHtml($str) ? Response::html($str) : Response::text($str);
            }
            if (method_exists($result, 'toArray')) {
                return Response::json($result->toArray());
            }
            return Response::json((array) $result);
        }

        if (is_int($result) || is_float($result) || is_bool($result)) {
            return Response::text((string) $result);
        }

        if ($result === null) {
            return Response::text('', HttpCode::NO_CONTENT);
        }

        return Response::text((string) $result);
    }

    /**
     * Check if a request path matches a path prefix/pattern.
     * Matches exact or child paths (e.g., /admin matches /admin, /admin/, /admin/users, but NOT /administer).
     */
    public static function matchPath(string $requestPath, string $prefix): bool
    {
        $prefix = '/' . ltrim($prefix, '/');
        $prefix = rtrim($prefix, '/');

        if ($prefix === '' || $prefix === '/') {
            return true;
        }

        $requestPath = '/' . ltrim($requestPath, '/');

        if ($requestPath === $prefix) {
            return true;
        }

        return str_starts_with($requestPath, $prefix . '/');
    }

    /**
     * Check if a request host matches a host pattern.
     * Supports exact match and wildcard (e.g., *.domain.com).
     */
    public static function matchHost(string $requestHost, string $hostPattern): bool
    {
        $requestHost = strtolower(trim(explode(':', $requestHost)[0]));
        $hostPattern = strtolower(trim(explode(':', $hostPattern)[0]));

        if ($requestHost === $hostPattern || $hostPattern === '*') {
            return true;
        }

        if (str_starts_with($hostPattern, '*.')) {
            $domain = substr($hostPattern, 2);
            return $requestHost === $domain || str_ends_with($requestHost, '.' . $domain);
        }

        return false;
    }

    /**
     * Check if content appears to be HTML.
     */
    public static function isHtml(string $content): bool
    {
        $trimmed = trim($content);
        return preg_match('/<\s*([a-z][a-z0-9]*)\b[^>]*>/i', $trimmed) === 1;
    }
}

