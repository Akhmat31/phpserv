<?php

namespace Source;

use Source\Http\Routing\Router;
//use Phxroute\Http\Routing\Route;
use Source\Http\Request;
use Source\Http\Response\Response;
use Source\Http\Response\HttpCode;
use Source\Http\HttpException;
use DI\Container;
use DI\ContainerBuilder;
use Throwable;
use Closure;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class Dispatcher
 * Handles route dispatching with dependency injection
 */
class Dispatcher
{
    private ?string $notFoundPath = null;

    private Container $container;

    public function __construct(?Container $container = null)
    {
        if ($container === null) {
            $builder = new ContainerBuilder();
            $builder->useAutowiring(true);
            $builder->useAttributes(true);
            $container = $builder->build();
        }
        
        $this->container = $container;
    }

    /**
     * Set custom 404 handler
     */
    public function set404(string $path): void
    {
        $this->notFoundPath = $path;
        Response::setErrorView(HttpCode::NOT_FOUND, $path);
    }

    /**
     * Set custom 401 Unauthorized handler
     */
    public function setUnauthorized(string $path): void
    {
        Response::setErrorView(HttpCode::UNAUTHORIZED, $path);
    }

    /**
     * Get the DI container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Dispatch the request to the appropriate route handler
     */
    public function dispatch(Router $router, Request $request): Response
    {
        try {
            // Bind request to container
            $this->container->set(Request::class, $request);
            
            $routeInfo = $router->matchRequest(
                $request->getMethod(),
                $request->getPath()
            );    
            
            if ($routeInfo === false) {
                return $this->handleNotFound();
            }

            $route = $routeInfo['route'];
            $handler = $routeInfo['handler'];
            $vars = $routeInfo['vars'] ?? [];

            // Inject route parameters into request
            foreach ($vars as $key => $value) {
                $request->setRouteParam($key, $value);
            }

            // Execute handler with dependency injection
            $result = $this->executeHandler($handler, $request, $vars);
            
            return $this->prepareResponse($result);

        } catch (HttpException $e) {
            if ($e->getCode() === 401) {
                return $this->handleUnauthorized();
            }
            return Response::json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (Throwable $e) {
            return Response::json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle 404 not found
     */
    private function handleNotFound(): Response
    {
        $viewPath = Response::getErrorView(HttpCode::NOT_FOUND);
        if ($viewPath && is_file($viewPath) && is_readable($viewPath)) {
            return Response::view($viewPath, [], HttpCode::NOT_FOUND);
        }
        return Response::json(["error" => "Route Not Found"], HttpCode::NOT_FOUND);
    }

    /**
     * Handle 401 Unauthorized
     */
    private function handleUnauthorized(): Response
    {
        $viewPath = Response::getErrorView(HttpCode::UNAUTHORIZED);
        if ($viewPath && is_file($viewPath) && is_readable($viewPath)) {
            return Response::view($viewPath, [], HttpCode::UNAUTHORIZED);
        }
        return Response::json(["error" => "Unauthorized"], HttpCode::UNAUTHORIZED);
    }

    /**
     * Execute handler with dependency injection
     */
    private function executeHandler(Closure|array|string $handler, Request $request, array $routeParams): mixed
    {
        // Start output buffering to catch any echo statements
        ob_start();
        
        try {
            $result = null;
            
            // Handle Closure
            if ($handler instanceof Closure) {
                $result = $this->executeClosureWithDI($handler, $request, $routeParams);
            }
            // Handle Controller@method or [Controller::class, 'method']
            elseif (is_array($handler) && count($handler) === 2) {
                [$controller, $method] = $handler;
                $result = $this->executeControllerMethod($controller, $method, $request, $routeParams);
            }
            // Handle 'Controller@method' string
            elseif (is_string($handler) && str_contains($handler, '@')) {
                [$controller, $method] = explode('@', $handler, 2);
                $result = $this->executeControllerMethod($controller, $method, $request, $routeParams);
            }
            else {
                throw new \RuntimeException('Invalid handler format');
            }
            
            // Get any echoed content
            $echoedContent = ob_get_clean();
            
            // Combine echoed content with result if needed
            if (!empty($echoedContent)) {
                if ($result instanceof Response) {
                    $existingContent = $result->getContent();
                    $result->setContent($echoedContent . $existingContent);
                } elseif ($result === null) {
                    $result = $echoedContent;
                }
            }
            
            return $result;
            
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Execute closure with dependency injection
     */
    private function executeClosureWithDI(Closure $closure, Request $request, array $routeParams): mixed
    {
        $reflection = new ReflectionFunction($closure);
        $parameters = $this->resolveParameters($reflection->getParameters(), $request, $routeParams);
        
        return $closure(...$parameters);
    }

    /**
     * Execute controller method with dependency injection
     */
    private function executeControllerMethod(string $controller, string $method, Request $request, array $routeParams): mixed
    {
        // Resolve controller from container
        $controllerInstance = $this->container->get($controller);
        
        if (!method_exists($controllerInstance, $method)) {
            throw new \RuntimeException("Method [{$method}] not found in controller [{$controller}]");
        }
        
        $reflection = new ReflectionMethod($controllerInstance, $method);
        $parameters = $this->resolveParameters($reflection->getParameters(), $request, $routeParams);
        
        return $controllerInstance->$method(...$parameters);
    }

    /**
     * Resolve method/function parameters with dependency injection
     */
    private function resolveParameters(array $reflectionParameters, Request $request, array $routeParams): array
    {
        $parameters = [];
        
        foreach ($reflectionParameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            
            // Check if parameter is in route parameters
            if (isset($routeParams[$name])) {
                $parameters[] = $routeParams[$name];
                continue;
            }
            
            // Try to resolve by type
            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                
                // Special case for Request
                if ($typeName === Request::class || is_subclass_of($typeName, Request::class)) {
                    $parameters[] = $request;
                    continue;
                }
                
                // Try to resolve from container
                try {
                    $parameters[] = $this->container->get($typeName);
                    continue;
                } catch (Throwable $e) {
                    // If can't resolve and parameter is optional, use default
                    if ($parameter->isOptional()) {
                        $parameters[] = $parameter->getDefaultValue();
                        continue;
                    }
                    throw $e;
                }
            }
            
            // If parameter has default value, use it
            if ($parameter->isOptional()) {
                $parameters[] = $parameter->getDefaultValue();
                continue;
            }
            
            throw new \RuntimeException("Cannot resolve parameter [{$name}]");
        }
        
        return $parameters;
    }

    /**
     * Prepare response from handler result
     */
    private function prepareResponse(mixed $result): Response
    {
        // If already a Response, return it
        if ($result instanceof Response) {
            return $result;
        }
        
        // If string, check if HTML or plain text
        if (is_string($result)) {
            if ($this->isHtml($result)) {
                return Response::html($result);
            }
            return Response::text($result);
        }
        
        // If array or object, return JSON
        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }
        
        // If null or empty, return empty response
        if ($result === null) {
            return Response::json(['error' => 'Handler returned no content'], 500);
        }
        
        // Convert to string
        return Response::text((string)$result);
    }

    /**
     * Check if content looks like HTML
     */
    private function isHtml(string $content): bool
    {
        $trimmed = trim($content);
        return preg_match('/<\s*([a-z][a-z0-9]*)\b[^>]*>/i', $trimmed) === 1;
    }
}