<?php
namespace PhxPlugins;
require __DIR__ . "/../vendor/autoload.php";
/**
 * ENTRY MAIN OF PLUGINS
 *
 * Autoloading for plugins is handled by the root composer.json, so this
 * file only provides the PhxPlugins facade used across the app.
 */
use PhxPlugins\Databaseutils\DB;
use PhxPlugins\Xcsrf\XcsrfToken;

class Features
{
    public static function initDatabase(array $config): void
    {
        DB::configure($config);
    }

    public static function db(): string
    {
        return DB::class;
    }
    public static function initXcsrfToken(): XcsrfToken
    {
        return new XcsrfToken();
    }
    public static function initXcsrfSession(): XcsrfToken
    {
        return new XcsrfToken();
    }
}