<?php

declare(strict_types = 1);

namespace Plattry\Http\Routing;

use Plattry\Http\Exception\NotFoundException;
use Plattry\Utils\Filesystem;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Router
 * @package Plattry\Http\Routing
 */
class Router implements RouterInterface
{
    /**
     * Routing rule root node
     * @var RuleTree
     */
    public RuleTree $root;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->root = new RuleTree();
    }

    /**
     * @inheritDoc
     */
    public function register(RuleInterface $rule): void
    {
        $replacement = sprintf("/%s", $this->root::WILDCARD);
        foreach ($rule->getMethods() as $method) {
            $path = preg_replace("#/:[^/.]+#", $replacement, $rule->getPath());
            $index = $this->splitIndex($method, $path);
            $this->root->addNode($index, $rule);
        }
    }

    /**
     * @inheritDoc
     */
    public function parse(ServerRequestInterface $request): RuleInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $index = $this->splitIndex($method, $path);
        $rule = $this->root->getNode($index);

        !$rule instanceof RuleInterface &&
        throw new NotFoundException("No matching routing rule exists with $path.");

        $args = [];
        foreach ($this->splitIndex($method, $rule->getPath()) as $i => $item) {
            if (str_starts_with($item, ":"))
                $args[substr($item, 1)] = $index[$i];
        }

        return (clone $rule)->withArgs($args);
    }

    /**
     * Split the request method and path into index array.
     * @param string $method
     * @param string $path
     * @return array
     */
    protected function splitIndex(string $method, string $path): array
    {
        $path = trim($path, "/");

        return [strtoupper($method), ...explode("/", $path)];
    }

    /**
     * Load route attribute from directory.
     * @param string $dirname
     * @return void
     * @throws ReflectionException
     */
    public function loadDir(string $dirname): void
    {
        $files = Filesystem::scanDir($dirname, true, "/.php$/");
        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * Load route attribute from file.
     * @param string $file
     * @return void
     * @throws ReflectionException
     */
    public function loadFile(string $file): void
    {
        $class = Filesystem::findClass($file);
        if (is_string($class) && class_exists($class))
            $this->loadClass($class);
    }

    /**
     * Load route attribute from class.
     * @param string $class
     * @return void
     * @throws ReflectionException
     */
    public function loadClass(string $class): void
    {
        $refClass = new ReflectionClass($class);
        $refMethods = $refClass->getMethods();
        foreach ($refMethods as $refMethod) {
            if (!str_starts_with($refMethod->getName(), "__"))
                $this->loadMethod($class, $refMethod->getName());
        }
    }

    /**
     * Load route attribute from method.
     * @param string $class
     * @param string $method
     * @return void
     * @throws ReflectionException
     */
    public function loadMethod(string $class, string $method): void
    {
        $routes = [];

        $refAttr = (new ReflectionClass($class))->getAttributes(Route::class)[0] ?? null;
        if ($refAttr instanceof ReflectionAttribute)
            $routes[] = $refAttr->newInstance();

        $refAttr = (new ReflectionMethod($class, $method))->getAttributes(Route::class)[0] ?? null;
        if (!$refAttr instanceof ReflectionAttribute)
            return;

        $args = array_merge($refAttr->getArguments(), ["class" => $class, "method" => $method]);
        $routes[] = (new ReflectionClass(Route::class))->newInstanceArgs($args);

        $rule = $this->generateRuleByRoutes(...$routes);
        $this->register($rule);
    }

    /**
     * Generate a rule by routes.
     * @param Route ...$routes
     * @return RuleInterface
     */
    protected function generateRuleByRoutes(Route ...$routes): RuleInterface
    {
        $route = array_reduce($routes, function ($front, $next) {
            $front = $front->toArray();
            $next = $next->toArray();

            return new Route(...[
                "path" => sprintf("%s/%s", $front['path'], $next['path']),
                "methods" => array_merge($front['methods'], $next['methods']),
                "middlewares" => array_merge($front['middlewares'], $next['middlewares']),
                "class" => $next['class'] ?: $front['class'],
                "method" => $next['method'] ?: $front['method']
            ]);
        }, new Route());

        return new Rule(...$route->toArray());
    }
}
