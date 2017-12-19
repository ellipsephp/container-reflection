<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Resolvable\Exceptions\ResolvingExceptionInterface;

class ReflectionContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class, ResolvingExceptionInterface $previous)
    {
        $template = "Failed to auto wire '%s' through the reflection container";

        $msg = sprintf($template, $class);

        parent::__construct($msg, 0, $previous);
    }
}
