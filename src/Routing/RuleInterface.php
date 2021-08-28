<?php

declare(strict_types = 1);

namespace Plattry\Http\Routing;

/**
 * Interface RuleInterface
 * @package Plattry\Http\Routing
 */
interface RuleInterface
{
    /**
     * RuleInterface constructor.
     * @param string $path
     * @param array $methods
     * @param array $middlewares
     * @param string $class
     * @param string $method
     */
    public function __construct(string $path, array $methods, array $middlewares, string $class, string $method);

    /**
     * Get request path.
     * @return string
     */
    public function getPath(): string;

    /**
     * Get methods.
     * @return array
     */
    public function getMethods(): array;

    /**
     * Get middlewares.
     * @return array
     */
    public function getMiddlewares(): array;

    /**
     * Get class.
     * @return string
     */
    public function getClass(): string;

    /**
     * Get method.
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get path args.
     * @return array
     */
    public function getArgs(): array;

    /**
     * Convert object to array.
     * @return array
     */
    public function toArray(): array;
}
