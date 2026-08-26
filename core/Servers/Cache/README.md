# Cache Subsystem

Provides a caching layer with pluggable drivers.

## Classes

- `Source\Cache\CacheManager` — Central manager, delegates to a driver, supports `remember()` and `getMultiple()`
- `Source\Cache\ArrayCache` — In-memory driver (lost on process exit)
- `Source\Cache\FileCache` — File-based persistent driver

## Usage

```php
use Source\Cache\CacheManager;

// File-based cache
$cache = CacheManager::createFromArray([
    'driver' => 'file',
    'path' => __DIR__ . '/cache',
]);

// Store
$cache->set('user.1', ['name' => 'Akhmat'], 3600);

// Retrieve
$user = $cache->get('user.1');

// Remember pattern
$data = $cache->remember('expensive.key', 60, fn() => computeSomething());

// Via Features facade
\PhxPlugins\Features::initCache(['driver' => 'file', 'path' => '/tmp/cache']);
\PhxPlugins\Features::cache()->get('key');
```

## Interface

All drivers implement `Source\Interface\CacheInterface`:
`get`, `set`, `delete`, `clean`, `has`, `getCacheInfo`.
