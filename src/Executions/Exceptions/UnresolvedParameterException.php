<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\Exceptions;

use RuntimeException;
use ReflectionParameter;

use Psr\Container\ContainerExceptionInterface;

class UnresolvedParameterException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(ReflectionParameter $parameter)
    {
        $msg = "No value can be inferred for parameter $%s.";

        parent::__construct(sprintf($msg, $parameter->getName()));
    }
}
