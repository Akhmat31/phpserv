<?php

declare(strict_types=1);

namespace PhxPlugins\Stratigility;

use PhxPlugins\Stratigility\Exception\EmptyPipelineException;
use Source\Http\Response\HttpCode;
use Source\Http\Response\Response;

class EmptyPipelineHandler
{
    private ?Response $responsePrototype;

    public function __construct(?Response $responsePrototype = null)
    {
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * Handle the request when no middleware handles it.
     *
     * @throws EmptyPipelineException
     */
    public function handle(mixed $request): Response
    {
        if ($this->responsePrototype !== null) {
            return $this->responsePrototype;
        }

        throw EmptyPipelineException::forPipeline();
    }

    /**
     * Allow invokable usage.
     */
    public function __invoke(mixed $request): Response
    {
        return $this->handle($request);
    }
}