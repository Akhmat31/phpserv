<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;

class DoublePassMiddlewareDecorator
{
    /** @var callable */
    private $middleware;
    private ?Response $responsePrototype;

    public function __construct(callable $middleware, ?Response $responsePrototype = null)
    {
        $this->middleware = $middleware;
        $this->responsePrototype = $responsePrototype ?? new Response();
    }

    /**
     * Process double-pass middleware signature: fn($request, $response, $next).
     */
    public function process(mixed $request, mixed $handler): Response
    {
        $response = $this->responsePrototype ?? new Response();

        $next = function (mixed $req) use ($handler): Response {
            if (is_object($handler) && method_exists($handler, 'handle')) {
                return Utils::autoResponse($handler->handle($req));
            }
            if (is_callable($handler)) {
                return Utils::autoResponse($handler($req));
            }
            return Utils::autoResponse($handler);
        };

        $result = ($this->middleware)($request, $response, $next);
        return Utils::autoResponse($result);
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

