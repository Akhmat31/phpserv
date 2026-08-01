<?php
require __DIR__ . "/vendor/autoload.php";
/**
 * ENTRY MAIN OF PLUGINS
 *
 * Autoloading for plugins is handled by the root composer.json, so this
 * file only provides the PhxPlugins facade used across the app.
 */

use PhxPlugins\Databaseutils\DB;

class PhxPlugins
{
    public static function initDatabase(array $config): void
    {
        DB::configure($config);
    }

    public static function db(): string
    {
        return DB::class;
    }
}
