<?php

declare(strict_types=1);

namespace Plattry\Http\Routing;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Route
 * @package Plattry\Http\Routing
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Route
{
    /**
     * Request path
     * @var string
     */
    protected string $path;

    /**
     * All the methods that allowed
     * @var array
     */
    protected array $methods;

    /**
     * All the middlewares that passes through
     * @var MiddlewareInterface[]
     */
    protected array $middlewares;

    /**
     * Target class
     * @var string
     */
    protected string $class;

    /**
     * Target method
     * @var string
     */
    protected string $method;

    /**
     * Route constructor.
     * @param string $path
     * @param array $methods
     * @param array $middlewares
     */
    public function __construct(string $path = "", array $methods = [], array $middlewares = [], string $class = "", string $method = "")
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->middlewares = $middlewares;
        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Get request path.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get methods.
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get middlewares.
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get class.
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get method.
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Convert object to array.
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
