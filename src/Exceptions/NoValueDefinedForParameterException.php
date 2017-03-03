<?php declare(strict_types=1);

namespace Pmall\Container\Exceptions;

use RuntimeException;
use ReflectionParameter;

class NoValueDefinedForParameterException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(ReflectionParameter $parameter)
    {
        $name = $parameter->getName();

        parent::__construct(sprintf('No value defined for parameter %s.', $name));
    }
}
