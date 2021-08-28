<?php

declare(strict_types = 1);

namespace Plattry\Http\Routing;

use Plattry\Http\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouterInterface
 * @package Plattry\Http\Routing
 */
interface RouterInterface
{
    /**
     * Register routing rules.
     * @param Rule $rule
     * @return void
     */
    public function register(Rule $rule): void;

    /**
     * Parse requests to routing rules.
     * @param ServerRequestInterface $request
     * @return RuleInterface
     * @throws NotFoundException
     */
    public function parse(ServerRequestInterface $request): RuleInterface;
}
