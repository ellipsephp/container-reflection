<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ImplementationNotDefinedException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Can\'t inject %s: the container does not contain any definition for this value.', $id));
    }
}
