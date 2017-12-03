<?php declare(strict_types=1);

namespace Ellipse\Container\Classes\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ClassNotInstantiableException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        $msg = "Class '%s' is not instantiable (interface or abstract).";

        parent::__construct(sprintf($msg, $class));
    }
}
