<?php

declare(strict_types = 1);

namespace Plattry\Http\Cookie;

/**
 * Class Cookie
 * @package Plattry\Http\Cookie
 */
class Cookie implements CookieInterface
{
    /**
     * New cookies
     * @var CookieElementInterface[]
     */
    protected array $queue = [];

    /**
     * @inheritDoc
     */
    public function make(string $name, string $value): CookieElementInterface
    {
        return $this->queue[] = new CookieElement($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function getQueue(): array
    {
        return $this->queue;
    }
}
