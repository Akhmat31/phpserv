<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use Source\Http\Response\HttpCode;
use Source\Http\Response\Response;
use Throwable;

class ErrorResponseGenerator
{
    private bool $debug;
    private ?Response $responsePrototype;

    public function __construct(bool $debug = false, ?Response $responsePrototype = null)
    {
        $this->debug = $debug;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * Generate an error response from an uncaught Throwable.
     */
    public function __invoke(Throwable $e, mixed $request, ?Response $response = null): Response
    {
        return $this->generate($e, $request, $response);
    }

    public function generate(Throwable $e, mixed $request, ?Response $response = null): Response
    {
        $statusCode = $this->determineStatusCode($e);

        $isJson = false;
        if (is_object($request) && method_exists($request, 'isJson')) {
            $isJson = $request->isJson();
        } elseif (is_object($request) && method_exists($request, 'header')) {
            $accept = (string) $request->header('Accept', '');
            $isJson = str_contains($accept, 'application/json');
        }

        if ($isJson) {
            $payload = [
                'error' => $e->getMessage(),
                'code' => $statusCode,
            ];
            if ($this->debug) {
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
                $payload['trace'] = $e->getTraceAsString();
            }
            return Response::json($payload, $statusCode);
        }

        if ($this->debug) {
            $html = sprintf(
                "<h1>500 Server Error</h1><p><strong>Message:</strong> %s</p><p><strong>File:</strong> %s:%d</p><pre>%s</pre>",
                htmlspecialchars($e->getMessage()),
                htmlspecialchars($e->getFile()),
                $e->getLine(),
                htmlspecialchars($e->getTraceAsString())
            );
            return Response::html($html, $statusCode);
        }

        return Response::html("<h1>Server Error</h1><p>An unexpected error occurred.</p>", $statusCode);
    }

    private function determineStatusCode(Throwable $e): int
    {
        $code = $e->getCode();
        if (is_int($code) && $code >= 400 && $code < 600) {
            return $code;
        }

        return HttpCode::INTERNAL_SERVER_ERROR;
    }
}

