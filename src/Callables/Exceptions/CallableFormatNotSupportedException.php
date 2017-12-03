<?php declare(strict_types=1);

namespace Ellipse\Container\Callables\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class CallableFormatNotSupportedException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(callable $callable)
    {
        $msg = "Unsupported callable format: %s.";

        parent::__construct(sprintf($msg, print_r($callable, true)));
    }
}
