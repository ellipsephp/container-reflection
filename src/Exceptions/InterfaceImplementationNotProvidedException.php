<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

class InterfaceImplementationNotProvidedException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $interface)
    {
        parent::__construct(sprintf('No implementation provided by the container for interface \'%s\'', $interface));
    }
}
