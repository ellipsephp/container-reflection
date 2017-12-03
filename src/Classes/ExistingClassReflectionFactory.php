<?php declare(strict_types=1);

namespace Ellipse\Container\Classes;

use ReflectionClass;

use Ellipse\Container\Classes\Exceptions\ClassNotFoundException;

class ExistingClassReflectionFactory implements ClassReflectionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(string $class): ReflectionClass
    {
        if (interface_exists($class) || class_exists($class)) {

            return new ReflectionClass($class);

        }

        throw new ClassNotFoundException($class);
    }
}
