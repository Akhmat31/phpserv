<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Source\Http\Request;
use Source\Http\Response\Response;

require_once __DIR__ . '/../helper/middleware.pipe.inc.php';

final class MiddlewarePipeTest extends TestCase
{
    public function testMiddlewareRunsBeforeAndAfterHandler(): void
    {
        $events = [];
        $request = new Request('GET', '/orders');
        $response = middleware_run($request, [
            function (Request $request, \Closure $next) use (&$events): Response {
                $events[] = 'before';
                $response = $next($request);
                $events[] = 'after';
                return $response;
            },
        ], function () use (&$events): Response {
            $events[] = 'handler';
            return Response::text('ok');
        });

        $this->assertSame(['before', 'handler', 'after'], $events);
        $this->assertSame('ok', $response->getContent());
    }

    public function testMiddlewareCanShortCircuit(): void
    {
        $handlerCalled = false;
        $response = middleware_run(new Request('GET', '/private'), [
            fn (Request $request, \Closure $next): Response => Response::text('blocked', 403),
        ], function () use (&$handlerCalled): Response {
            $handlerCalled = true;
            return Response::text('ok');
        });

        $this->assertFalse($handlerCalled);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testPathMatchingIncludesNestedPaths(): void
    {
        $this->assertTrue(middleware_matches('/admin/users', '/admin'));
        $this->assertFalse(middleware_matches('/administer', '/admin'));
    }
}
