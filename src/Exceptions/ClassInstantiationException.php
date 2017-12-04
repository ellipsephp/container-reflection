<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ClassInstantiationException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class, UnresolvedParameterException $delegate)
    {
        $msg = "The class '%s' instantiation failed because $%s value can't be resolved:\n-%s";

        parent::__construct(sprintf($msg, $class, $delegate->parameter()->getName(), $delegate->getMessage()));
    }
}
