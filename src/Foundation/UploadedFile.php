<?php

declare(strict_types = 1);

namespace Plattry\Http\Foundation;

use Plattry\Http\Exception\InvalidArgumentException;
use Plattry\Http\Exception\RuntimeException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UploadedFile
 * @package Plattry\Http\Foundation
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * Uploaded file stream
     * @var StreamInterface
     */
    protected StreamInterface $stream;

    /**
     * Uploaded file size
     * @var int|null
     */
    protected int|null $size;

    /**
     * Uploaded error code
     * @var int
     */
    protected int $error;

    /**
     * Temp filename
     * @var string
     */
    protected string $tmp_path;

    /**
     * File Move Status
     * @var bool
     */
    protected bool $moved = false;

    /**
     * Client filename
     * @var string|null
     */
    protected string|null $client_filename;

    /**
     * Client media type
     * @var string|null
     */
    protected string|null $client_media_type;

    /**
     * Error code && phrase
     * @var array
     */
    protected static array $error_code = [
        UPLOAD_ERR_OK => "UPLOAD_ERR_OK",
        UPLOAD_ERR_INI_SIZE => "UPLOAD_ERR_INI_SIZE",
        UPLOAD_ERR_FORM_SIZE => "UPLOAD_ERR_FORM_SIZE",
        UPLOAD_ERR_PARTIAL => "UPLOAD_ERR_PARTIAL",
        UPLOAD_ERR_NO_FILE => "UPLOAD_ERR_NO_FILE",
        UPLOAD_ERR_NO_TMP_DIR => "UPLOAD_ERR_NO_TMP_DIR",
        UPLOAD_ERR_CANT_WRITE => "UPLOAD_ERR_CANT_WRITE",
        UPLOAD_ERR_EXTENSION => "UPLOAD_ERR_EXTENSION"
    ];

    /**
     * UploadedFile constructor.
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @throws InvalidArgumentException
     */
    public function __construct(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    )
    {
        !isset(static::$error_code[$error]) &&
        throw new InvalidArgumentException("Uploaded error code must be 0 ~ 8.");

        $this->stream = $stream;
        $this->size = $size;
        $this->error = $error;
        $this->tmp_path = (string)$stream->getMetadata('uri');
        $this->client_filename = $clientFilename;
        $this->client_media_type = $clientMediaType;
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function moveTo($targetPath)
    {
        $this->moved &&
        throw new RuntimeException("An error occurred while moving to $targetPath.");

        $dirname = dirname($targetPath);

        !is_dir($dirname) && !mkdir($dirname, 0777, true) &&
        throw new RuntimeException("An error occurred while making $dirname.");

        $this->stream->rewind();
        $fd = fopen($targetPath, 'w');

        do {
            $string = $this->stream->read(65535);
            if ($string === "")
                break;

            fwrite($fd, $string);
        } while (true);

        fclose($fd);

        $this->moved = true;
        $this->stream->close();
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int|null
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Get error phrase.
     * @return string
     */
    public function getErrorPhrase(): string
    {
        return static::$error_code[$this->error];
    }

    /**
     * Get temporary filename.
     * @return string
     */
    public function getTmpPath(): string
    {
        return $this->tmp_path;
    }

    /**
     * Check whether it has been moved.
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): string|null
    {
        return $this->client_filename;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): string|null
    {
        return $this->client_media_type;
    }
}
