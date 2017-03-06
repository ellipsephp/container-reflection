<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Acclimate\Container\Decorator\AbstractContainerDecorator;

use Ellipse\Container\Exceptions\NoValueDefinedForParameterException;

class ReflectionContainer extends AbstractContainerDecorator
{
    /**
     * Make an instance of the given class.
     *
     * @param string $class
     * @param array  $overrides
     * @param array  $defaults
     * @return mixed
     */
    public function make(string $class, array $overrides = [], array $defaults = [])
    {
        $reflection = new ReflectionClass($class);

        if ($constructor = $reflection->getConstructor()) {

            $parameters = $constructor->getParameters();

            $values = $this->getResolvedParameters($parameters, $overrides, $defaults);

            return $reflection->newInstanceArgs($values);

        }

        return new $class;
    }

    /**
     * Execute a callable with the given overrides as parameters. Retrieve the
     * remaining parameters from the container.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @param array     $defaults
     * @return mixed
     */
    public function call(callable $callable, array $overrides = [], array $defaults = [])
    {
        if (is_string($callable) and strpos($callable, '::') !== false) {

            $callable = explode('::', $callable);

        }

        $reflection = is_array($callable)
            ? new ReflectionMethod($callable[0], $callable[1])
            : new Reflectionfunction($callable);

        $parameters = $reflection->getParameters();

        $values = $this->getResolvedParameters($parameters, $overrides, $defaults);

        if (! is_array($callable)) {

            return $reflection->invokeArgs($values);

        }

        $instance = ! $reflection->isStatic() ? $callable[0] : null;

        return $reflection->invokeArgs($instance, $values);
    }

    /**
     * Resolve the list of values to use as parameters from the given list of
     * reflection parameters and the given overrides.
     *
     * @param array $parameters
     * @param array $overrides
     * @param array $defaults
     * @return array
     * @throws \Ellipse\Container\Exceptions\NoValueDefinedForParameterException
     */
    private function getResolvedParameters(array $parameters, array $overrides, array $defaults): array
    {
        // get scalars parameters.
        $scalars = array_filter($parameters, function (ReflectionParameter $parameter) {

            return is_null($parameter->getClass());

        });

        // get names of scalar parameters.
        $names = array_map(function (ReflectionParameter $scalar) {

            return $scalar->getName();

        }, $scalars);

        // get the defaults with a key named as a scalar parameter.
        $named = array_filter($defaults, function ($key) use ($names) {

            return in_array((string) $key, $names);

        }, ARRAY_FILTER_USE_KEY);

        // get the defaults with a key not named as a scalar parameter.
        $defaults = array_filter($defaults, function ($key) use ($names) {

            return ! in_array((string) $key, $names);

        }, ARRAY_FILTER_USE_KEY);

        // return a value for each parameter.
        return array_map(function (ReflectionParameter $parameter) use ($overrides, $named, &$defaults) {

            // when the parameter is type hinted as a class try to return an
            // override named like this class. If no override is named like this
            // then retrieve an instance of this class from the container.
            if ($class = $parameter->getClass()) {

                $name = $class->getName();

                return array_key_exists($name, $overrides)
                    ? $overrides[$name]
                    : $this->container->get($name);

            }

            // priority to defaults having the same name as the parameter.
            $name = $parameter->getName();

            if (array_key_exists($name, $named)) return $named[$name];

            // try to get a default value.
            if (count($defaults) > 0) {

                $value = array_shift($defaults);

                if (! $value instanceof DefaultValue) return $value;

            }

            // finally try to return the defined default value if any.
            if ($parameter->isDefaultValueAvailable()) {

                return $parameter->getDefaultValue();

            }

            // fail when no value found.
            throw new NoValueDefinedForParameterException($parameter);

        }, $parameters);
    }
}
