<?php

namespace Source\Interface;

/**
 * Interface Cache
 *
 * Contract for cache store implementations.
 * Inspired by CodeIgniter 4's caching interface.
 */
interface CacheInterface
{
    /**
     * Retrieve an item from the cache.
     *
     * @param string $key Cache item key
     * @return mixed The cached value or null if not found
     */
    public function get(string $key): mixed;

    /**
     * Store an item in the cache.
     *
     * @param string $key Cache item key
     * @param mixed $value The value to store
     * @param int $ttl Time-to-live in seconds (0 = forever)
     */
    public function set(string $key, mixed $value, int $ttl = 60): bool;

    /**
     * Remove an item from the cache.
     *
     * @param string $key Cache item key
     */
    public function delete(string $key): bool;

    /**
     * Remove all items from the cache.
     */
    public function clean(): bool;

    /**
     * Get cache statistics.
     */
    public function getCacheInfo(): array;

    /**
     * Check if an item exists in the cache.
     *
     * @param string $key Cache item key
     */
    public function has(string $key): bool;
}
