<?php

declare(strict_types = 1);

namespace Plattry\Http\Session;

use Plattry\Http\Exception\InvalidArgumentException;

/**
 * Interface SessionInterface
 * @package Plattry\Http\Session
 */
interface SessionInterface
{
    /**
     * Session constructor.
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver);

    /**
     * Get session name.
     * @return string
     */
    public static function getName(): string;

    /**
     * Set session name.
     * @param string $name
     * @return void
     * @throws InvalidArgumentException
     */
    public static function setName(string $name): void;

    /**
     * Get session id.
     * @return false|string
     */
    public function getId(): false|string;

    /**
     * Set session id.
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * Get session expire time.
     * @return int
     */
    public function getExpire(): int;

    /**
     * Set session expire time(seconds).
     * @param int $expire
     * @return void
     */
    public function setExpire(int $expire): void;

    /**
     * Check if a key exists.
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Get session value by key.
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed;

    /**
     * Set session key && value.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, mixed $value): void;

    /**
     * Delete session value by key.
     * @param string $name
     * @return void
     */
    public function del(string $name): void;

    /**
     * Save current session.
     * @return void
     */
    public function save(): void;

    /**
     * Destroy current session.
     * @return void
     */
    public function destroy(): void;
}
