# API Subsystem

Provides standardized JSON API responses and rate limiting.

## Classes

- `Source\API\ApiResponse` — Fluent JSON response builder
- `Source\API\RateLimiter` — Token-bucket rate limiter backed by Cache

## ApiResponse Usage

```php
use Source\API\ApiResponse;

// Success
return ApiResponse::success($user, 'User retrieved')
    ->toResponse();

// With pagination
return ApiResponse::success($users)
    ->withPagination(1, 20, 100, 5)
    ->toResponse();

// Error
return ApiResponse::error('Not found', 404)->toResponse();

// Validation error
return ApiResponse::validationError([
    'email' => 'Email is required',
    'name'  => 'Name is too short',
])->toResponse();

// Via Features facade
return \PhxPlugins\Features::apiSuccess($data, 'Done')->toResponse();
```

## RateLimiter Usage

```php
use Source\API\RateLimiter;

$limiter = RateLimiter::getInstance();

// Check rate limit
if (!$limiter->attempt('ip:127.0.0.1', 100, 60)) {
    return ApiResponse::tooManyRequests()->toResponse();
}

// Or throw exception
$limiter->attemptOrThrow('ip:127.0.0.1', 100, 60);

// Get remaining attempts
$remaining = $limiter->remainingAttempts('ip:127.0.0.1', 100);

// Via Features facade
\PhxPlugins\Features::rateLimiter()->attempt('user:1', 60, 60);
```

## Response Format

Success:
```json
{ "success": true, "data": {...}, "message": "...", "meta": {...} }
```

Error:
```json
{ "success": false, "error": { "code": 404, "message": "..." }, "message": "..." }
```
