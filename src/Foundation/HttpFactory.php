<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Plattry\Http\Exception\InvalidArgumentException;
use Plattry\Http\Exception\RuntimeException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class HttpFactory
 * @package Plattry\Http\Foundation
 */
class HttpFactory implements
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return (new Request([], $this->createStream()))
            ->withMethod($method)
            ->withUri($uri instanceof  UriInterface ? $uri : $this->createUri($uri));
    }

    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response([], $this->createStream()))->withStatus($code, $reasonPhrase);
    }

    /**
     * @inheritDoc
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new ServerRequest([], $this->createStream(), $serverParams))
            ->withMethod($method)
            ->withUri($uri instanceof  UriInterface ? $uri : $this->createUri($uri))
            ->withRequestTarget((string) $uri);
    }

    /**
     * @inheritDoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'plattry_stream_');

        $tmpPath === false &&
        throw new RuntimeException("An error occurred while creating a new stream.");

        file_put_contents($tmpPath, $content);

        return new Stream(fopen($tmpPath, "a+"));
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new Stream(fopen($filename, $mode));
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    /**
     * @inheritDoc
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * @inheritDoc
     */
    public function createUri(string $uri = ''): UriInterface
    {
        $info = parse_url($uri);

        $info === false &&
        throw new InvalidArgumentException("$uri is a seriously malformed uri.");

        return (new Uri())->withScheme($info['scheme'] ?? "")
            ->withUserInfo($info['user'] ?? "", $info['pass'] ?? null)
            ->withHost($info['host'] ?? "")
            ->withPort(intval($info['port'] ?? ""))
            ->withPath($info['path'] ?? "")
            ->withQuery($info['query'] ?? "")
            ->withFragment($info['fragment'] ?? "");
    }
}
