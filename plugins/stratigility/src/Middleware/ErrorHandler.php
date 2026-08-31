<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility\Middleware;

use PhxPlugins\Stratigility\Utils;
use Source\Http\Response\Response;
use Throwable;

class ErrorHandler
{
    private ?Response $responsePrototype;
    /** @var callable */
    private $responseGenerator;
    /** @var callable[] */
    private array $listeners = [];

    public function __construct(?Response $responsePrototype = null, ?callable $responseGenerator = null)
    {
        $this->responsePrototype = $responsePrototype;
        $this->responseGenerator = $responseGenerator ?? new ErrorResponseGenerator(false, $responsePrototype);
    }

    /**
     * Attach an error listener callback: fn(Throwable $e, $request, Response $response).
     */
    public function attachListener(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * Catch errors occurring in the handler pipeline and generate an error response.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        try {
            if (is_object($handler) && method_exists($handler, 'handle')) {
                return Utils::autoResponse($handler->handle($request));
            }
            if (is_callable($handler)) {
                return Utils::autoResponse($handler($request));
            }
            return Utils::autoResponse($handler);
        } catch (Throwable $e) {
            $response = ($this->responseGenerator)($e, $request, $this->responsePrototype);
            $response = Utils::autoResponse($response);

            foreach ($this->listeners as $listener) {
                $listener($e, $request, $response);
            }

            return $response;
        }
    }

    public function __invoke(mixed $request, mixed $handler): Response
    {
        return $this->process($request, $handler);
    }
}

