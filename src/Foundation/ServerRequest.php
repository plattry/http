<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Plattry\Http\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class ServerRequest
 * @package Plattry\Http\Foundation
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Customer attributes
     * @var array
     */
    protected array $attributes = [];

    /**
     * Query params
     * @var array
     */
    protected array $query = [];

    /**
     * Parsed body params
     * @var array|object|null
     */
    protected array|object|null $request = [];

    /**
     * Cookie params
     * @var array
     */
    protected array $cookies = [];

    /**
     * Uploaded files
     * @var UploadedFileInterface[]
     */
    protected array $files = [];

    /**
     * Server params
     * @var array
     */
    protected array $server = [];

    /**
     * ServerRequest constructor.
     * @param array $headers
     * @param StreamInterface $body
     * @param array $server
     */
    public function __construct(array $headers, StreamInterface $body, array $server = [])
    {
        $this->server = $server;
        parent::__construct($headers, $body);
    }

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies): static
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->files;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles): static
    {
        foreach ($uploadedFiles as $uploadedFile) {
            !$uploadedFile instanceof UploadedFileInterface &&
            throw new InvalidArgumentException("Uploadedfiles must be the instances of UploadedFileInterface.");
        }

        $this->files = $uploadedFiles;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody(): array|object|null
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data): static
    {
        $this->request = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name): static
    {
        unset($this->attributes[$name]);

        return $this;
    }
}
