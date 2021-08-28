<?php

declare(strict_types = 1);

namespace Plattry\Http\Exception;

/**
 * Class InvalidArgumentException
 * @package Plattry\Http\Exception
 */
class InvalidArgumentException extends \InvalidArgumentException implements HttpExceptionInterface
{
}