<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use Source\Http\Response\HttpCode;
use Source\Http\Response\Response;

class NotFoundHandler
{
    private ?Response $responsePrototype;

    public function __construct(?Response $responsePrototype = null)
    {
        $this->responsePrototype = $responsePrototype;
    }

    public function handle(mixed $request): Response
    {
        if ($this->responsePrototype !== null) {
            return $this->responsePrototype->setStatusCode(HttpCode::NOT_FOUND);
        }

        $viewPath = Response::getErrorView(HttpCode::NOT_FOUND);
        if ($viewPath && is_file($viewPath) && is_readable($viewPath)) {
            return Response::view($viewPath, [], HttpCode::NOT_FOUND);
        }

        $isJson = false;
        if (is_object($request) && method_exists($request, 'isJson')) {
            $isJson = $request->isJson();
        }

        if ($isJson) {
            return Response::json(['error' => 'Not Found', 'code' => HttpCode::NOT_FOUND], HttpCode::NOT_FOUND);
        }

        return Response::html('<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>', HttpCode::NOT_FOUND);
    }

    public function process(mixed $request, mixed $handler): Response
    {
        return $this->handle($request);
    }

    public function __invoke(mixed $request, mixed $handler = null): Response
    {
        return $this->handle($request);
    }
}

