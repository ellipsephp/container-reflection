<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class CallableExecutionException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(callable $callable, UnresolvedParameterException $delegate)
    {
        $msg = "The given callable execution failed because $%s value can't be resolved:\n-%s";

        parent::__construct(sprintf($msg, $delegate->parameter()->getName(), $delegate->getMessage()));
    }
}
