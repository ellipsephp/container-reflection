<?php declare(strict_types=1);

namespace Ellipse\Container\Classes;

use ReflectionClass;

interface ClassReflectionFactoryInterface
{
    /**
     * Return a new ReflectionClass from the given class name.
     *
     * @param string $class
     * @return \ReflectionClass
     */
    public function __invoke(string $class): ReflectionClass;
}
