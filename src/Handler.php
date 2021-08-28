<?php

declare(strict_types = 1);

namespace Plattry\Http;

use Plattry\Http\Foundation\HttpFactory;
use Plattry\Http\Routing\RuleInterface;
use Plattry\Ioc\Container;
use Plattry\Ioc\ContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Handler
 * @package Plattry\Http
 */
class Handler implements RequestHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * All the middlewares that passes through
     * @var array
     */
    protected array $middlewares = [];

    /**
     * Target class
     * @var string
     */
    protected string $class = "";

    /**
     * Target method
     * @var string
     */
    protected string $method = "";

    /**
     * Rule args
     * @var array
     */
    protected array $args = [];

    /**
     * Handler constructor.
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->middlewares = $rule->getMiddlewares();
        $this->class = $rule->getClass();
        $this->method = $rule->getMethod();
        $this->args = $rule->getArgs();

        $this->container = new Container();
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = next($this->middlewares);

        if ($middleware === false)
            return $this->call($request);

        return $this->container->get($middleware)->process($request, $this);
    }

    /**
     * Call rule target.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function call(ServerRequestInterface $request): ResponseInterface
    {
        if (method_exists($this->class, $this->method)) {
            $controller = $this->container->get($this->class);

            return $controller->{$this->method}($request, $this->args);
        }

        return (new HttpFactory())->createResponse();
    }
}
