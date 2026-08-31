<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Middleware\CallableMiddlewareDecorator;
use PhxPlugins\Stratigility\Middleware\HostMiddlewareDecorator;
use PhxPlugins\Stratigility\Middleware\PathMiddlewareDecorator;
use Source\Http\Response\Response;
use SplQueue;

class Pipeline
{
    /** @var SplQueue */
    private SplQueue $pipeline;

    /**
     * @param array $middleware Initial list of middleware
     */
    public function __construct(array $middleware = [])
    {
        $this->pipeline = new SplQueue();
        foreach ($middleware as $item) {
            $this->pipe($item);
        }
    }

    /**
     * Attach middleware to the pipeline with optional path and host filtering.
     */
    public function pipe(mixed $middleware, ?string $path = null, ?string $host = null): self
    {
        $item = $middleware;

        if ($path !== null) {
            $item = new PathMiddlewareDecorator($path, $item);
        }

        if ($host !== null) {
            $item = new HostMiddlewareDecorator($host, $item);
        }

        $this->pipeline->enqueue($item);

        return $this;
    }

    /**
     * Attach middleware scoped to a specific path prefix.
     */
    public function pipePath(string $path, mixed $middleware): self
    {
        return $this->pipe($middleware, path: $path);
    }

    /**
     * Attach middleware scoped to a specific host.
     */
    public function pipeHost(string $host, mixed $middleware): self
    {
        return $this->pipe($middleware, host: $host);
    }

    /**
     * Process request through the pipeline with a terminal fallback handler.
     */
    public function process(mixed $request, mixed $handler): Response
    {
        $next = new Next($this->pipeline, $handler);
        return $next->handle($request);
    }

    /**
     * Handle request through the pipeline with default empty handler if no handler provided.
     */
    public function handle(mixed $request): Response
    {
        $next = new Next($this->pipeline, new EmptyPipelineHandler());
        return $next->handle($request);
    }

    /**
     * Run request through the pipeline.
     */
    public function run(mixed $request, mixed $handler = null): Response
    {
        if ($handler !== null) {
            return $this->process($request, $handler);
        }

        return $this->handle($request);
    }

    /**
     * Get all registered middleware in an array.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        $items = [];
        $queue = clone $this->pipeline;
        while (!$queue->isEmpty()) {
            $items[] = $queue->dequeue();
        }
        return $items;
    }

    /**
     * Allow invokable pipeline execution.
     */
    public function __invoke(mixed $request, mixed $handler = null): Response
    {
        return $this->run($request, $handler);
    }
}

