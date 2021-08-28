<?php

declare(strict_types = 1);

namespace Plattry\Http\Routing;

use Plattry\Http\Exception\InvalidArgumentException;

/**
 * Class Rule
 * @package Plattry\Http\Routing
 */
class Rule implements RuleInterface
{
    /**
     * Request path
     * @var string
     */
    protected string $path;

    /**
     * All the methods that allowed
     * @var array|string[]
     */
    protected array $methods;

    /**
     * All the middlewares that passes through
     * @var array
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
     * Path args
     * @var array
     */
    protected array $args;

    /**
     * @inheritDoc
     */
    public function __construct(string $path, array $methods, array $middlewares, string $class, string $method)
    {
        $this->path = (string)preg_replace(["#[ ]#", "#/+#"], ["", "/"], sprintf("/%s", $path));

        $this->methods = array_map(fn($method) => strtoupper($method), $methods);

        foreach ($middlewares as $middleware) {
            !class_exists($middleware) &&
            throw new InvalidArgumentException("Class $class does not exist.");
        }

        $this->middlewares = $middlewares;

        !class_exists($class) &&
        throw new InvalidArgumentException("Class $class does not exist.");

        !method_exists($class, $method) &&
        throw new InvalidArgumentException("Method $method does not exist in class $class.");

        $this->class = $class;
        $this->method = $method;

        $this->args = [];
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @inheritDoc
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set path args.
     * @param array $args
     * @return static
     */
    public function withArgs(array $args): static
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
