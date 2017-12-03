<?php declare(strict_types=1);

namespace Ellipse\Container\Classes\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ClassNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        $msg = "Class '%s' not found.";

        parent::__construct(sprintf($msg, $class));
    }
}
