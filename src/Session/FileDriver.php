<?php

declare(strict_types = 1);

namespace Plattry\Http\Session;

/**
 * Class FileDriver
 * @package Plattry\Http\Session
 */
class FileDriver implements DriverInterface
{
    /**
     * Base directory
     * @var string
     */
    protected static string $path = '/tmp';

    /**
     * File prefix
     * @var string
     */
    protected static string $prefix = 'plattry_session';

    /**
     * Get storage path.
     * @return string
     */
    public static function getPath(): string
    {
        return self::$path;
    }

    /**
     * Set storage path.
     * @param string $path
     * @return void
     */
    public static function setPath(string $path): void
    {
        self::$path = $path;
    }

    /**
     * Get file prefix.
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::$prefix;
    }

    /**
     * Set file prefix.
     * @param string $prefix
     * @return void
     */
    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    /**
     * Get session filename.
     * @param string $id
     * @return string
     */
    protected function getKey(string $id): string
    {
        return sprintf("%s/%s_%s", static::$path, static::$prefix, $id);
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): array
    {
        $filename = $this->getKey($id);
        if (!file_exists($filename))
            return ["", []];

        $content = file_get_contents($filename);
        if ($content === false)
            return ["", []];

        [$data, $validTime] = unserialize($content);
        if ($validTime < time()) {
            unlink($filename);
            return ["", []];
        }

        return [$id, $data];
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, array $data, int $expire): void
    {
        $filename = $this->getKey($id);
        $mkTime = file_exists($filename) ? (filectime($filename) ?: time()) : time();
        $content = serialize([$data, $mkTime + $expire]);

        file_put_contents($filename, $content);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): void
    {
        $filename = $this->getKey($id);
        file_exists($filename) && unlink($filename);
    }
}
