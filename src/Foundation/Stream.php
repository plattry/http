<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Plattry\Http\Exception\InvalidArgumentException;
use Plattry\Http\Exception\RuntimeException;
use Psr\Http\Message\StreamInterface;

/**
 * Class Stream
 * @package Plattry\Http\Foundation
 */
class Stream implements StreamInterface
{
    /**
     * File descriptor
     * @var resource
     */
    protected mixed $fd;

    /**
     * File path
     * @var string
     */
    protected string $path;

    /**
     * Stream constructor.
     * @param mixed $fd
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $fd)
    {
        !is_resource($fd) &&
        throw new InvalidArgumentException("Stream fd must be a valid resource.");

        $this->fd = $fd;
        $this->path = (string)$this->getMetadata("uri");
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        fclose($this->fd);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int|null
    {
        $stat = fstat($this->fd);

        if ($stat === false)
            return null;

        return $stat['size'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        $pos = ftell($this->fd);

        $pos === false &&
        throw new RuntimeException("An error occurred while getting the current position of the pointer.");

        return $pos;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return feof($this->fd);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return (bool)fseek($this->fd, 0, SEEK_CUR);
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->fd, $offset, $whence) === -1 &&
        throw new RuntimeException("An error occurred while seeking the pointer.");
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        !rewind($this->fd) &&
        throw new RuntimeException("An error occurred while rewinding the pointer.");
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return fwrite($this->fd, "") !== false;
    }

    /**
     * @inheritDoc
     */
    public function write($string)
    {
        fwrite($this->fd, $string) === false &&
        throw new RuntimeException("An error occurred while writing data.");
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return fread($this->fd, 0) !== false;
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        $string = fread($this->fd, $length);

        $string === false &&
        throw new RuntimeException("An error occurred while writing data.");

        return $string;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        $content = "";
        rewind($this->fd);

        while (true) {
            $string = fread($this->fd, 65535);

            $string === false &&
            throw new RuntimeException("An error occurred while getting file content.");

            if ($string === "")
                break;

            $content .= $string;
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null): mixed
    {
        $data = stream_get_meta_data($this->fd);

        if (is_null($key))
            return $data;

        return $data[$key] ?? null;
    }

    /**
     * Stream destructor.
     * @throws RuntimeException
     */
    public function __destruct()
    {
        file_exists($this->path) && !unlink($this->path) &&
        throw new RuntimeException("An error occurred while unlinking $this->path.");
    }
}
