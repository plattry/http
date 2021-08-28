<?php

declare(strict_types = 1);

namespace Plattry\Http\Cookie;

use Plattry\Http\Exception\InvalidArgumentException;

/**
 * Interface CookieInterface
 * @package Plattry\Http\Cookie
 */
interface CookieInterface
{
    /**
     * Generate a new cookie record and put it in queue.
     * @param string $name
     * @param string $value
     * @return CookieElementInterface
     * @throws InvalidArgumentException
     */
    public function make(string $name, string $value): CookieElementInterface;

    /**
     * Get all new cookie record from queue.
     * @return CookieElementInterface[]
     */
    public function getQueue(): array;
}
