<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

class AbstractClassImplementationNotProvidedException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('No implementation provided by the container for the abstract class \'%s\'', $class));
    }
}
