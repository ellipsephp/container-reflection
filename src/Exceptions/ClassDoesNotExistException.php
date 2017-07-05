<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

class ClassDoesNotExistException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Class \'%s\' not found', $class));
    }
}
