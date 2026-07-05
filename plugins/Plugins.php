<?php

/**
 * ENTRY MAIN OF PLUGINS
 */

// Load PhxDatabaseUtils
require __DIR__ . "/PhxDatabaseUtils/vendor/autoload.php";

use PhxPlugins\Databaseutils\DB;

class PhxPlugins {
    public static function initDatabase(array $config): void { DB::configure($config); }
    public static function db(): string { return DB::class; }


    
}
