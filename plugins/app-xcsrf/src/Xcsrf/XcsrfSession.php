<?php

declare(strict_types=1);

namespace PhxPlugins\Xcsrf;

class XcsrfSession
{
    protected static function ensureStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * @return mixed|null
     */
    public static function get(string $key)
    {
        self::ensureStarted();

        return $_SESSION[$key] ?? null;
    }

    /**
     * @param mixed $value
     */
    public static function set(string $key, $value): void
    {
        self::ensureStarted();

        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();

        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();

        unset($_SESSION[$key]);
    }

    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::ensureStarted();

        session_regenerate_id($deleteOldSession);
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}