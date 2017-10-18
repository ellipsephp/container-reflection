<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ClassNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Can\'t make %s: this is not an interface or class name.', $id));
    }
}
