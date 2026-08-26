<?php

namespace Source\Cache;

use Source\Interface\CacheInterface;

/**
 * Class CacheManager
 *
 * Central cache manager that delegates to a configurable driver.
 * Supports switching drivers at runtime and provides a factory
 * for common driver types.
 */
class CacheManager
{
    private CacheInterface $driver;
    private static ?CacheManager $instance = null;

    public function __construct(CacheInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get the shared singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(new ArrayCache());
        }

        return self::$instance;
    }

    /**
     * Set the singleton instance (useful for testing).
     */
    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Create a cache manager from a config array.
     *
     * Supported drivers: 'array', 'file'
     * File driver requires a 'path' key.
     */
    public static function createFromArray(array $config): self
    {
        $driver = $config['driver'] ?? 'array';

        $instance = match ($driver) {
            'file' => new self(new FileCache($config['path'] ?? sys_get_temp_dir() . '/phx_cache')),
            'array' => new self(new ArrayCache()),
            default => throw new \InvalidArgumentException("Unknown cache driver: {$driver}"),
        };

        self::setInstance($instance);

        return $instance;
    }

    /**
     * Switch to a different driver at runtime.
     */
    public function setDriver(CacheInterface $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get the active driver.
     */
    public function getDriver(): CacheInterface
    {
        return $this->driver;
    }

    public function get(string $key): mixed
    {
        return $this->driver->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        return $this->driver->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    public function clean(): bool
    {
        return $this->driver->clean();
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function getCacheInfo(): array
    {
        return $this->driver->getCacheInfo();
    }

    /**
     * Remember: get from cache or execute callback and cache the result.
     *
     * @param string $key Cache key
     * @param int $ttl Time-to-live in seconds
     * @param callable $callback Factory for the value if not cached
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->driver->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->driver->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get multiple cache items at once.
     *
     * @param array $keys List of cache keys
     * @return array Key-value map of found items
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $value = $this->driver->get($key);
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
