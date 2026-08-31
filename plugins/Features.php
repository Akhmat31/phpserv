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
use PhxPlugins\Stratigility\Pipeline;
use PhxPlugins\Xcsrf\XcsrfToken;
use Source\Cache\CacheManager;
use Source\Encryption\EncryptionManager;
use Source\I18n\I18n;
use Source\API\ApiResponse;
use Source\API\RateLimiter;
use Source\Validation;

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
    public static function pipeline(array $middleware = []): Pipeline
    {
        return new Pipeline($middleware);
    }

    public static function initXcsrfToken(): XcsrfToken
    {
        return new XcsrfToken();
    }
    public static function initXcsrfSession(): XcsrfToken
    {
        return new XcsrfToken();
    }

    /**
     * Initialize the cache subsystem.
     *
     * @param array $config ['driver' => 'array'|'file', 'path' => '...']
     */
    public static function initCache(array $config = []): CacheManager
    {
        return CacheManager::createFromArray($config);
    }

    /**
     * Get the shared cache manager instance.
     */
    public static function cache(): CacheManager
    {
        return CacheManager::getInstance();
    }

    /**
     * Initialize the encryption subsystem with a key.
     *
     * @param string $key Encryption key (at least 32 bytes for AES-256)
     */
    public static function initEncryption(string $key): EncryptionManager
    {
        return new EncryptionManager($key);
    }

    /**
     * Initialize the internationalization subsystem.
     *
     * @param string $langPath Path to language files directory
     */
    public static function initI18n(string $langPath): I18n
    {
        $instance = new I18n($langPath);
        I18n::setInstance($instance);

        return $instance;
    }

    /**
     * Get the shared i18n instance.
     */
    public static function i18n(): I18n
    {
        return I18n::getInstance();
    }

    /**
     * Create a new validation instance.
     */
    public static function validation(): Validation
    {
        return new Validation();
    }

    /**
     * Create a successful API response.
     */
    public static function apiSuccess(mixed $data = null, string $message = '', int $statusCode = 200): ApiResponse
    {
        return ApiResponse::success($data, $message, $statusCode);
    }

    /**
     * Create an error API response.
     */
    public static function apiError(string $message, int $statusCode = 400, mixed $errorData = null): ApiResponse
    {
        return ApiResponse::error($message, $statusCode, $errorData);
    }

    /**
     * Get the shared rate limiter instance.
     */
    public static function rateLimiter(): RateLimiter
    {
        return RateLimiter::getInstance();
    }
}
