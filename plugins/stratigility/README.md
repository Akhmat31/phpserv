# Stratigility Middleware Plugin for Phx

A robust, PSR-15 inspired and PSR-7/Phx-compatible middleware pipeline execution system.

## Features

- **Pipeline Runner**: FIFO middleware queue processing with support for nested handlers, short-circuiting, and fallback handlers.
- **Double Pass & Single Pass**: Compatible with both `fn($request, $handler)` and double-pass `fn($request, $response, $next)` callable formats.
- **Path & Host Scoping**: Easy routing decorators to apply middleware conditionally based on URL prefix (`path()`) or host/domain (`host()`).
- **Error Handling**: Built-in `ErrorHandler` and `ErrorResponseGenerator` for graceful exception catching and debugging.
- **Seamless Integration**: Fully integrated with Phx router, dispatcher, and `config/middleware.load.inc.php`.

## Usage Example

```php
use PhxPlugins\Stratigility\Pipeline;
use Source\Http\Request;
use Source\Http\Response\Response;

$pipeline = new Pipeline();

// Add middleware
$pipeline->pipe(function (Request $request, $next) {
    // Before logic
    $response = $next($request);
    // After logic
    return $response;
});

// Run with handler
$response = $pipeline->process($request, function ($request) {
    return Response::text('Hello World');
});
```

