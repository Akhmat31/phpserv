<?php

namespace Source\API;

use Source\Cache\CacheManager;
use Source\Cache\ArrayCache;
use Source\Http\HttpException;
use Source\Http\Response\HttpCode;

/**
 * Class RateLimiter
 *
 * Token-bucket / fixed-window rate limiter for API endpoints.
 * Inspired by CodeIgniter 4's Throttle class and Laravel's RateLimiter.
 *
 * Uses the Cache subsystem to track request counts per identifier
 * (e.g. IP address or user ID).
 */
class RateLimiter
{
    private CacheManager $cache;
    private static ?RateLimiter $instance = null;

    public function __construct(?CacheManager $cache = null)
    {
        $this->cache = $cache ?? CacheManager::getInstance();
    }

    /**
     * Get the shared singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the shared singleton instance.
     */
    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Attempt a request under the given rate limit.
     *
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param int $maxAttempts Maximum attempts allowed in the window
     * @param int $windowSeconds Time window in seconds
     * @return bool True if the request is allowed, false if rate-limited
     */
    public function attempt(string $identifier, int $maxAttempts, int $windowSeconds): bool
    {
        $key = "rate_limit:{$identifier}";
        $data = $this->cache->get($key);

        if ($data === null) {
            $this->cache->set($key, [
                'count' => 1,
                'reset_at' => time() + $windowSeconds,
            ], $windowSeconds);

            return true;
        }

        if (!is_array($data)) {
            $this->cache->set($key, [
                'count' => 1,
                'reset_at' => time() + $windowSeconds,
            ], $windowSeconds);

            return true;
        }

        if ($data['count'] >= $maxAttempts) {
            return false;
        }

        $data['count']++;
        $this->cache->set($key, $data, $windowSeconds);

        return true;
    }

    /**
     * Attempt a request or throw a 429 Too Many Requests exception.
     *
     * @throws HttpException
     */
    public function attemptOrThrow(string $identifier, int $maxAttempts, int $windowSeconds): void
    {
        if (!$this->attempt($identifier, $maxAttempts, $windowSeconds)) {
            throw HttpException::tooManyRequests(
                "Rate limit exceeded. Maximum {$maxAttempts} requests per {$windowSeconds} seconds."
            );
        }
    }

    /**
     * Get the number of remaining attempts for an identifier.
     *
     * @param string $identifier Unique identifier
     * @param int $maxAttempts Maximum attempts in the window
     * @return int Remaining attempts (0 if exhausted)
     */
    public function remainingAttempts(string $identifier, int $maxAttempts): int
    {
        $key = "rate_limit:{$identifier}";
        $data = $this->cache->get($key);

        if ($data === null || !is_array($data)) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - ($data['count'] ?? 0));
    }

    /**
     * Get the timestamp when the rate limit resets.
     *
     * @param string $identifier Unique identifier
     * @return int|null Unix timestamp, or null if no limit is active
     */
    public function getResetTime(string $identifier): ?int
    {
        $key = "rate_limit:{$identifier}";
        $data = $this->cache->get($key);

        if ($data === null || !is_array($data)) {
            return null;
        }

        return $data['reset_at'] ?? null;
    }

    /**
     * Clear the rate limit for a given identifier.
     */
    public function clear(string $identifier): bool
    {
        return $this->cache->delete("rate_limit:{$identifier}");
    }
}
