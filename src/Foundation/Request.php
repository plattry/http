<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Request
 * @package Plattry\Http\Foundation
 */
class Request extends Message implements RequestInterface
{
    /**
     * Request method
     * @var string
     */
    protected string $method;

    /**
     * Request target
     * @var string
     */
    protected string $target;
    
    /**
     * Request uri
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        if ($this->target)
            return $this->target;

        if ($this->uri)
            return (string)$this->uri;

        return "/";
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget): static
    {
        $this->target = $requestTarget;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $this->uri = $uri;

        return $this;
    }
}
