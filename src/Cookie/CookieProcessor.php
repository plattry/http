<?php

declare(strict_types = 1);

namespace Plattry\Http\Cookie;

use Plattry\Http\Processor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CookieProcessor
 * @package Plattry\Http\Cookie
 */
class CookieProcessor extends Processor
{
    /**
     * Cookie instance
     * @var CookieInterface
     */
    protected CookieInterface $cookie;

    /**
     * CookieProcessor constructor.
     * @param CookieInterface $cookie
     */
    public function __construct(CookieInterface $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * @inheritDoc
     */
    protected function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $values = array_map(fn($cookieElement) => (string)$cookieElement, $this->cookie->getQueue());
        if (!empty($values))
            $response->withAddedHeader('Set-Cookie', $values);

        return parent::after($request, $response);
    }
}
