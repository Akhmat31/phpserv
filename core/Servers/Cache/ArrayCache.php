<?php

namespace Source\Cache;

use Source\Interface\CacheInterface;

/**
 * Class ArrayCache
 *
 * In-memory cache driver. Data is lost when the process ends.
 * Useful for testing and short-lived request caching.
 */
class ArrayCache implements CacheInterface
{
    private array $store = [];
    private array $expiries = [];

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->store[$key];
    }

    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        $this->store[$key] = $value;
        $this->expiries[$key] = $ttl > 0 ? time() + $ttl : 0;

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->store[$key], $this->expiries[$key]);

        return true;
    }

    public function clean(): bool
    {
        $this->store = [];
        $this->expiries = [];

        return true;
    }

    public function getCacheInfo(): array
    {
        return [
            'driver' => 'array',
            'count' => count($this->store),
            'keys' => array_keys($this->store),
        ];
    }

    public function has(string $key): bool
    {
        if (!array_key_exists($key, $this->store)) {
            return false;
        }

        $expiry = $this->expiries[$key] ?? 0;
        if ($expiry > 0 && time() > $expiry) {
            $this->delete($key);

            return false;
        }

        return true;
    }
}
