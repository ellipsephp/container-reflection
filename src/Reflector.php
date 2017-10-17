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
     * Return an array containing the reflected parameters of the given callable.
     *
     * @param callable $callable
     * @return array
     */
    public function getReflectedParameters(callable $callable): array
    {
        return $this->getReflectedCallable($callable)->getParameters();
    }

    /**
     * Return a reflection function abstract from any callable.
     *
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    private function getReflectedCallable(callable $callable): ReflectionFunctionAbstract
    {
        // function () {}
        if ($callable instanceof Closure) {

            return new ReflectionFunction($callable);

        }

        // [$instance, 'method'] or [SomeClass::class, 'method']
        if (is_array($callable)) {

            return new ReflectionMethod($callable[0], $callable[1]);

        }

        // new class () { public function __invoke () {} }
        if (is_object($callable)) {

            return new ReflectionMethod($callable, '__invoke');

        }

        // 'SomeClass::method'
        if (is_string($callable) && strpos($callable, '::') !== false) {

            return new ReflectionMethod($callable);

        }

        // 'somefunction'
        return new ReflectionFunction($callable);
    }
}
