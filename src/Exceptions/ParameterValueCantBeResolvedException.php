<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;
use ReflectionParameter;

class ParameterValueCantBeResolvedException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(ReflectionParameter $parameter)
    {
        $name = $parameter->getName();

        parent::__construct(sprintf('Value can\'t be resolved for the parameter \'$%s\'', $name));
    }
}
