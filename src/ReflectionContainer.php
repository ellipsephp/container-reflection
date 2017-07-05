<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Psr\Container\ContainerInterface;

use Ellipse\Container\Exceptions\ClassDoesNotExistException;
use Ellipse\Container\Exceptions\InterfaceImplementationNotProvidedException;
use Ellipse\Container\Exceptions\AbstractClassImplementationNotProvidedException;
use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

class ReflectionContainer implements ContainerInterface
{
    /**
     * The underlying container to decorate.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * Set up a reflection container with the underlying container to decorate.
     *
     * @param \Psr\Container\ContainerInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @{inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @{inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Make an instance of the given class.
     *
     * @param string $class
     * @param array  $overrides
     * @param array  $values
     * @return mixed
     * @throws Ellipse\Container\Exceptions\ClassDoesNotExistException
     * @throws Ellipse\Container\Exceptions\InterfaceImplementationNotProvidedException
     * @throws Ellipse\Container\Exceptions\AbstractClassImplementationNotProvidedException
     */
    public function make(string $class, array $overrides = [], array $values = [])
    {
        // when the alias is an interface name return what is provided by the
        // container or fail.
        if (interface_exists($class)) {

            if ($this->has($class)) return $this->get($class);

            throw new InterfaceImplementationNotProvidedException($class);

        }

        // fail when the alias is not an existing class name.
        if (! class_exists($class)) {

            throw new ClassDoesNotExistException($class);

        }

        // returns whats provided by the container for this alias if any.
        if ($this->has($class)) return $this->get($class);

        // get a reflection of the class.
        $reflection = new ReflectionClass($class);

        // fail when the alias is an abstract class name.
        if ($reflection->isAbstract()) {

            throw new AbstractClassImplementationNotProvidedException($class);

        }

        // get the class constructor.
        $constructor = $reflection->getConstructor();

        // when the class has no constructor just return a new instance.
        if (! $constructor) return new $class;

        // get the constructor parameters.
        $parameters = $constructor->getParameters();

        // try to resolve those parameters.
        $values = $this->getResolvedParameters($parameters, $overrides, $values);

        // return an instance of the class.
        return $reflection->newInstanceArgs($values);
    }

    /**
     * Execute a callable with the given overrides as parameters. Retrieve the
     * remaining parameters from the container.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @param array     $values
     * @return mixed
     */
    public function call(callable $callable, array $overrides = [], array $values = [])
    {
        // make a callable array from a callable string.
        if (is_string($callable) and strpos($callable, '::') !== false) {

            $callable = explode('::', $callable);

        }

        // reflect a class method or a function according to the callable type.
        $reflection = is_array($callable)
            ? new ReflectionMethod($callable[0], $callable[1])
            : new ReflectionFunction($callable);

        // resolve the callable parameters values.
        $parameters = $reflection->getParameters();

        // try to resolve those parameters.
        $values = $this->getResolvedParameters($parameters, $overrides, $values);

        // when the callable is a function call it using those values.
        if ($reflection instanceof ReflectionFunction) {

            return $reflection->invokeArgs($values);

        }

        // otherwise get the method's class and call the method with the
        // resolved parameters values. The class instance is null when the
        // method is static.
        $instance = ! $reflection->isStatic() ? $callable[0] : null;

        return $reflection->invokeArgs($instance, $values);
    }

    /**
     * Resolve the list of values to use as parameters from the given list of
     * reflection parameters and the given overrides.
     *
     * @param array $parameters
     * @param array $overrides
     * @param array $values
     * @return array
     * @throws \Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException
     */
    private function getResolvedParameters(array $parameters, array $overrides, array $values): array
    {
        // add the container to the overrides so it can be injected.
        $overrides = array_merge([ContainerInterface::class => $this->container], $overrides);

        // resolve all the parameters.
        return array_map(function (ReflectionParameter $parameter) use ($overrides, &$values) {

            // when the parameter is type hinted as a class try to return an
            // override named like this class. If no override is named like this
            // then retrieve an instance of this class from the container when
            // it contains the classname or make a new instance.
            if ($class = $parameter->getClass()) {

                $name = $class->getName();

                if (array_key_exists($name, $overrides)) return $overrides[$name];

                return $this->make($name, $overrides);

            }

            // get the next value in the list.
            if (count($values) > 0) return array_shift($values);

            // finally try to return the parameter default value if any.
            if ($parameter->isDefaultValueAvailable()) {

                return $parameter->getDefaultValue();

            }

            // fail when no value found.
            throw new ParameterValueCantBeResolvedException($parameter);

        }, $parameters);
    }
}
