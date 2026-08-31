<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;

class OriginalMessages
{
    public const ORIGINAL_REQUEST_ATTRIBUTE = 'originalRequest';
    public const ORIGINAL_URI_ATTRIBUTE = 'originalUri';

    /**
     * Store original request information before delegating to handler.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        if (is_object($request) && method_exists($request, 'setRouteParam')) {
            $request->setRouteParam('_original_path', method_exists($request, 'getPath') ? $request->getPath() : '/');
            $request->setRouteParam('_original_method', method_exists($request, 'getMethod') ? $request->getMethod() : 'GET');
        }

        if (is_object($handler) && method_exists($handler, 'handle')) {
            return Utils::autoResponse($handler->handle($request));
        }
        if (is_callable($handler)) {
            return Utils::autoResponse($handler($request));
        }

        return Utils::autoResponse($handler);
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

