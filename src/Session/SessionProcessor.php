<?php

declare(strict_types = 1);

namespace Plattry\Http\Session;

use Plattry\Http\Processor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SessionProcessor
 * @package Plattry\Http\Session
 */
class SessionProcessor extends Processor
{
    /**
     * Session instance
     * @var SessionInterface
     */
    protected SessionInterface $session;

    /**
     * SessionProcessor constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Get session id from request.
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getSessionId(ServerRequestInterface $request): string
    {
        $cookies = $request->getCookieParams();
        $sessionName = $this->session::getName();

        return $cookies[$sessionName] ?? '';
    }

    /**
     * @inheritDoc
     */
    protected function before(ServerRequestInterface $request): ResponseInterface|null
    {
        $sessionId = $this->getSessionId($request);

        $this->session->setId($sessionId);

        return parent::before($request);
    }

    /**
     * @inheritDoc
     */
    protected function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->session->save();

        $sessionId = $this->getSessionId($request);
        if (0 !== strcmp($sessionId, $this->session->getId())) {
            $value = sprintf(
                "%s=%s;path=/;expires=%s",
                $this->session::getName(),
                $this->session->getId(),
                gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT", time() + $this->session->getExpire())
            );
            $response->withAddedHeader('Set-Cookie', $value);
        }

        return parent::after($request, $response);
    }
}
