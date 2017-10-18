<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Closure;

class Reflector
{
    /**
     * Return a reflected class from the given class name.
     *
     * @param string $class
     * @return \Ellipse\Container\ReflectedClass
     */
    public function getReflectedClass(string $class): ReflectedClass
    {
        $reflection = new ReflectionClass($class);

        return new ReflectedClass($reflection);
    }

    /**
     * Return an array of reflected parameters from the given callable.
     *
     * @param callable $callable
     * @return array
     */
    public function getReflectedParameters(callable $callable): array
    {
        $reflections = $this->getReflectedCallable($callable)->getParameters();

        return array_map(function ($reflection) {

            return new ReflectedParameter($reflection);

        }, $reflections);
    }

    /**
     * Return a reflection function abstract from any callable.
     *
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    private function getReflectedCallable(callable $callable): ReflectionFunctionAbstract
    {
        // handle function () {}
        if ($callable instanceof Closure) {

            return new ReflectionFunction($callable);

        }

        // handle 'somefunction'
        if (is_string($callable) && strpos($callable, '::') === false) {

            return new ReflectionFunction($callable);

        }

        // handle [$instance, 'method'] or [SomeClass::class, 'method']
        if (is_array($callable)) {

            return new ReflectionMethod($callable[0], $callable[1]);

        }

        // handle new class () { public function __invoke () {} }
        if (is_object($callable)) {

            return new ReflectionMethod($callable, '__invoke');

        }

        // handle 'SomeClass::method'
        return new ReflectionMethod($callable);
    }
}
