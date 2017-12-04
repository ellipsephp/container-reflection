<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;
use ReflectionParameter;

use Psr\Container\ContainerExceptionInterface;

class UnresolvedParameterException extends RuntimeException implements ContainerExceptionInterface
{
    private $parameter;

    public function __construct(ReflectionParameter $parameter, ContainerExceptionInterface $delegate)
    {
        $this->parameter = $parameter;

        parent::__construct($delegate->getMessage());
    }

    public function parameter(): ReflectionParameter
    {
        return $this->parameter;
    }
}
