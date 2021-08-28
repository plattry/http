<?php

declare(strict_types = 1);

namespace Plattry\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Processor
 * @package Plattry\Http
 */
class Processor implements MiddlewareInterface
{
    /**
     * Action before calling target.
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    protected function before(ServerRequestInterface $request): ResponseInterface|null
    {
        return null;
    }

    /**
     * Action after calling target.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @inheritDoc
     */
    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->before($request);

        if ($response instanceof ResponseInterface)
            return $response;

        $response = $handler->handle($request);

        return $this->after($request, $response);
    }
}
