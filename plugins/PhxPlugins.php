<?php
require __DIR__ . "/vendor/autoload.php";
/**
 * ENTRY MAIN OF PLUGINS
 *
 * Autoloading for plugins is handled by the root composer.json, so this
 * file only provides the PhxPlugins facade used across the app.
 */

use PhxPlugins\Databaseutils\DB;
use PhxPlugins\Xcsrf\XcsrfSession;
use PhxPlugins\Xcsrf\XcsrfToken;

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
    /**
     * Instance XcsrfToken untuk generate/menampilkan token CSRF di form.
     * (mis. dipakai untuk $csrf->field('contact_form'))
     */
    public static function initXcsrfToken(): XcsrfToken
    {
        return new XcsrfToken();
    }

    /**
     * Instance XcsrfToken untuk memvalidasi token CSRF saat request masuk.
     * (mis. dipakai untuk $csrf->verifyRequest('contact_form'))
     *
     * Catatan: sengaja mengembalikan XcsrfToken (bukan XcsrfSession),
     * karena method verifyRequest()/validateToken() ada di XcsrfToken.
     * Data token itu sendiri tetap tersimpan di $_SESSION lewat
     * XcsrfSession secara statis, jadi instance manapun akan selalu
     * membaca token session yang sama.
     */
    public static function initXcsrfSession(): XcsrfToken
    {
        return new XcsrfToken();
    }
}