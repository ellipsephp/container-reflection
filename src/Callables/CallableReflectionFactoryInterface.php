<?php declare(strict_types=1);

namespace Ellipse\Container\Callables;

use ReflectionFunctionAbstract;

interface CallableReflectionFactoryInterface
{
    /**
     * Return a new ReflectionFunctionAbstract from the given callable.
     *
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    public function __invoke(callable $callable): ReflectionFunctionAbstract;
}
