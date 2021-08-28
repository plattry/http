<?php

declare(strict_types = 1);

namespace Plattry\Http\Cookie;

use Plattry\Http\Exception\InvalidArgumentException;
use Stringable;

/**
 * Interface CookieElementInterface
 * @package Plattry\Http\Cookie
 */
interface CookieElementInterface extends Stringable
{
    /**
     * CookieElementInterface constructor.
     * @param string $name
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $value);

    /**
     * Set cookie expire time(seconds).
     * @param int $time
     * @return static
     */
    public function withExpire(int $time): static;

    /**
     * Enable cookie permanent or not.
     * @param bool $permanent
     * @return static
     */
    public function withPermanent(bool $permanent): static;

    /**
     * Set cookie domain.
     * @param string $domain The domain.
     * @return static
     */
    public function withDomain(string $domain): static;

    /**
     * Set cookie path.
     * @param string $path The path.
     * @return static
     */
    public function withPath(string $path): static;

    /**
     * Enable cookie secure or not.
     * @param bool $secure
     * @return static
     */
    public function withSecure(bool $secure): static;

    /**
     * Enable cookie httponly or not.
     * @param bool $httpOnly
     * @return static
     */
    public function withHttpOnly(bool $httpOnly): static;

    /**
     * Set cookie sameSite such as none, lax and strict.
     * @param string $sameSite
     * @return static
     * @throws InvalidArgumentException
     */
    public function withSameSite(string $sameSite): static;
}
