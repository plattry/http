<?php

declare(strict_types = 1);

namespace Plattry\Http\Session;

use Plattry\Http\Exception\InvalidArgumentException;

/**
 * Class Session
 * @package Plattry\Http\Session
 */
class Session implements SessionInterface
{
    /**
     * Session storage driver
     * @var DriverInterface
     */
    protected DriverInterface $driver;

    /**
     * Session name
     * @var string
     */
    protected static string $name = 'PHPSESSID';

    /**
     * Session id
     * @var string
     */
    protected string $id = '';

    /**
     * Session expiration time
     * @var int
     */
    protected int $expire = 1800;

    /**
     * Session data
     * @var array
     */
    protected array $data = [];

    /**
     * @inheritDoc
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * @inheritDoc
     */
    public static function setName(string $name): void
    {
        empty($name) &&
        throw new InvalidArgumentException("Session name cannot be empty string.");

        static::$name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getId(): false|string
    {
        return $this->id ?: false;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): void
    {
        [$id, $data] = $this->driver->read($id);

        $this->id = $id ?: $this->generateId();
        $this->data = $data;
    }

    /**
     * Create a new session id.
     * @return string
     */
    protected function generateId(): string
    {
        return session_create_id();
    }

    /**
     * @inheritDoc
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @inheritDoc
     */
    public function setExpire(int $expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? false;
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function del(string $name): void
    {
        unset($this->data[$name]);
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        if ($this->id === "")
            $this->id = $this->generateId();

        $this->driver->write($this->id, $this->data, $this->expire);
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->driver->destroy($this->id);

        $this->id = $this->generateId();
        $this->data = [];
    }
}
