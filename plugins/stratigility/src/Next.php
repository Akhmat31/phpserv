<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use Closure;
use PhxPlugins\Stratigility\Exception\MiddlewarePipeNextHandlerAlreadyCalledException;
use PhxPlugins\Stratigility\Exception\MissingResponseException;
use Source\Http\Response\Response;
use SplQueue;

class Next
{
    private SplQueue $queue;
    private mixed $fallbackHandler;
    private bool $hasBeenCalled = false;

    /**
     * @param SplQueue $queue Queue of middleware to execute
     * @param mixed $fallbackHandler Fallback handler when the queue is exhausted
     */
    public function __construct(SplQueue $queue, mixed $fallbackHandler = null)
    {
        $this->queue = clone $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    /**
     * Process the next middleware in the queue.
     *
     * @throws MiddlewarePipeNextHandlerAlreadyCalledException
     * @throws MissingResponseException
     */
    public function handle(mixed $request): Response
    {
        if ($this->hasBeenCalled) {
            throw MiddlewarePipeNextHandlerAlreadyCalledException::create();
        }

        $this->hasBeenCalled = true;

        if ($this->queue->isEmpty()) {
            return $this->handleFallback($request);
        }

        $middleware = $this->queue->dequeue();
        $next = new self($this->queue, $this->fallbackHandler);

        $result = $this->dispatch($middleware, $request, $next);

        return Utils::autoResponse($result);
    }

    /**
     * Allow invokable usage so Next can be passed directly as Closure / callable.
     */
    public function __invoke(mixed $request): Response
    {
        return $this->handle($request);
    }

    /**
     * Dispatch the current middleware item.
     */
    private function dispatch(mixed $middleware, mixed $request, self $next): mixed
    {
        if (is_object($middleware) && method_exists($middleware, 'process')) {
            return $middleware->process($request, $next);
        }

        if (is_callable($middleware)) {
            return $middleware($request, $next);
        }

        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle($request);
        }

        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            return $this->dispatch($instance, $request, $next);
        }

        throw new MissingResponseException('Unable to dispatch middleware of type ' . get_debug_type($middleware));
    }

    /**
     * Execute fallback handler when the middleware queue is empty.
     */
    private function handleFallback(mixed $request): Response
    {
        if ($this->fallbackHandler === null) {
            $emptyHandler = new EmptyPipelineHandler();
            return $emptyHandler->handle($request);
        }

        if ($this->fallbackHandler instanceof Closure || is_callable($this->fallbackHandler)) {
            $result = ($this->fallbackHandler)($request);
            return Utils::autoResponse($result);
        }

        if (is_object($this->fallbackHandler) && method_exists($this->fallbackHandler, 'handle')) {
            $result = $this->fallbackHandler->handle($request);
            return Utils::autoResponse($result);
        }

        return Utils::autoResponse($this->fallbackHandler);
    }
}

