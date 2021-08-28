<?php

declare(strict_types = 1);

namespace Plattry\Http\Cookie;

use Plattry\Http\Exception\InvalidArgumentException;

/**
 * Class CookieElement
 * @package Plattry\Http\Cookie
 */
class CookieElement implements CookieElementInterface
{
    /**
     * Cookie name
     * @var string
     */
    protected string $name = '';

    /**
     * Cookie value be formatted
     * @var string
     */
    protected string $value = '';

    /**
     * Cookie expire time
     * @var string
     */
    protected string $expire = '';

    /**
     * Cookie permanent
     * @var bool
     */
    protected bool $permanent = false;

    /**
     * Cookie domain
     * @var string
     */
    protected string $domain = '';

    /**
     * Cookie path
     * @var string
     */
    protected string $path = '/';

    /**
     * Cookie secure
     * @var bool
     */
    protected bool $secure = false;

    /**
     * Cookie httpOnly
     * @var bool
     */
    protected bool $http_only = false;

    /**
     * Cookie sameSite
     * @var string
     */
    protected string $same_site = 'none';

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $value)
    {
        empty($name) &&
        throw new InvalidArgumentException("Cookie name cannot be empty.");

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function withExpire(int $time): static
    {
        $this->expire = gmstrftime(
            "%A, %d-%b-%Y %H:%M:%S GMT", time() + $time
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withPermanent(bool $permanent): static
    {
        $this->permanent = $permanent;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withSecure(bool $secure): static
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withHttpOnly(bool $httpOnly): static
    {
        $this->http_only = $httpOnly;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withSameSite(string $sameSite): static
    {
        $sameSite = strtolower($sameSite);

        ! in_array($sameSite, ['none', 'lax', 'strict']) &&
        throw new InvalidArgumentException("Cookie SameSite must be none, lax and strict.");

        $this->same_site = $sameSite;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $raw = "$this->name=$this->value";
        $raw .= empty($this->expire) ? '' : "; Expires:$this->expire";
        $raw .= false === $this->permanent ? '' : "; Max-Age:$this->expire";
        $raw .= empty($this->domain) ? '' : "; Domain:$this->domain";
        $raw .= empty($this->path) ? '' : "; Path:$this->path";
        $raw .= false === $this->secure ? '' : "; Secure";
        $raw .= false === $this->http_only ? '' : "; HttpOnly";
        $raw .= empty($this->same_site) ? '' : "; SameSite:$this->same_site";

        return $raw;
    }
}
