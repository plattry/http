<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Psr\Http\Message\StreamInterface;

/**
 * Class Stream
 * @package Plattry\Http\Foundation
 */
class Stream implements StreamInterface
{
    /**
     * Content string
     * @var string
     */
    protected string $str = "";

    /**
     * Position equal to offset bytes
     * @var int
     */
    protected int $ptr = 0;

    /**
     * Meta data
     * @var array
     */
    protected array $meta = [
        "timed_out" => false,
        "blocked" => true,
        "eof" => false,
        "unread_bytes" => 0,
        "seekable" => true,
    ];

    /**
     * Stream constructor.
     * @param string $content
     */
    public function __construct(string $content = "")
    {
        $this->str = $content;
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
        $this->str = "";
        $this->ptr = 0;
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        $stream = new static($this->str);

        $this->close();

        return $stream;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int|null
    {
        return strlen($this->str);
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return $this->ptr;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return !isset($this->str[$this->ptr]);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $ptr = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->ptr + $offset,
            SEEK_END => strlen($this->str) + $offset
        };

        ($ptr < 0 || $ptr > strlen($this->str)) &&
        throw new \RuntimeException("Out of range while seeking the pointer.");

        $this->ptr = $ptr;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->ptr = 0;
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        $ptr = $this->ptr;
        $this->ptr += strlen($string);

        $this->str = substr($this->str, 0, $ptr) . $string;

        return strlen($string);
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        $ptr = $this->ptr;
        $this->ptr = min($this->ptr + $length, strlen($this->str));

        return substr($this->str, $ptr, $length);
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        $ptr = $this->ptr;
        $this->ptr = strlen($this->str);

        return substr($this->str, $ptr);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null): mixed
    {
        if (is_null($key))
            return $this->meta;

        return $this->meta[$key] ?? null;
    }
}
