<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Plattry\Http\Exception\InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Message
 * @package Plattry\Http\Foundation
 */
class Message implements MessageInterface
{
    /**
     * Protocol version
     * @var string
     */
    protected string $version;

    /**
     * Input header
     * @var string[][]
     */
    protected array $headers;

    /**
     * Body stream
     * @var StreamInterface
     */
    protected StreamInterface $body;

    /**
     * Message constructor.
     * @param array $headers
     * @param StreamInterface $body
     */
    public function __construct(array $headers, StreamInterface $body)
    {
        $this->withProtocolVersion("1.0")->withHeaders($headers)->withBody($body);
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): static
    {
        $this->version = (string)$version;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        $values = $this->headers[$name] ?? [];

        return implode(';', $values);
    }

    /**
     * Return an instance with the provided value replacing the headers.
     * @param array $headers
     * @return static
     */
    public function withHeaders(array $headers): static
    {
        $this->headers = [];

        foreach ($headers as $name => $header)
            $this->withHeader($name, $header);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): static
    {
        unset($this->headers[$name]);

        $this->withAddedHeader($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): static
    {
        if (is_string($value))
            $value = "Set-Cookie" === $name ? [$value] : explode(";", $value);

        (!is_array($value) || empty($value)) &&
        throw new InvalidArgumentException("Http header value must be string and string[].");

        foreach ($value as $item)
            $this->headers[$name][] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name): static
    {
        unset($this->headers[$name]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): static
    {
        $this->body = $body;

        return $this;
    }
}
