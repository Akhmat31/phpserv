<?php

namespace Source\Cache;

use Source\Interface\CacheInterface;

/**
 * Class FileCache
 *
 * File-based cache driver. Persists cached data to disk.
 * Inspired by CodeIgniter 4's file cache handler.
 */
class FileCache implements CacheInterface
{
    private string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/\\');

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0775, true);
        }
    }

    public function get(string $key): mixed
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return null;
        }

        $contents = @file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }

        $data = @unserialize($contents);
        if ($data === false || !is_array($data)) {
            @unlink($filePath);

            return null;
        }

        if (isset($data['expiry']) && $data['expiry'] > 0 && time() > $data['expiry']) {
            @unlink($filePath);

            return null;
        }

        return $data['value'] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expiry' => $ttl > 0 ? time() + $ttl : 0,
            'created' => time(),
        ];

        return (bool) @file_put_contents($filePath, serialize($data), LOCK_EX);
    }

    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return @unlink($filePath);
        }

        return true;
    }

    public function clean(): bool
    {
        $files = glob($this->cachePath . '/*.cache');

        if ($files === false) {
            return true;
        }

        foreach ($files as $file) {
            @unlink($file);
        }

        return true;
    }

    public function getCacheInfo(): array
    {
        $files = glob($this->cachePath . '/*.cache') ?: [];

        return [
            'driver' => 'file',
            'path' => $this->cachePath,
            'count' => count($files),
        ];
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    private function getFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);

        return $this->cachePath . '/' . $safeKey . '.cache';
    }
}
