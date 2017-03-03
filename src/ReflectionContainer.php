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
     * @return mixed
     */
    public function make(string $class, array $overrides)
    {
        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        $parameters = $constructor->getParameters();

        $values = $this->getResolvedParameters($parameters, $overrides);

        return $reflection->newInstanceArgs($values);
    }

    /**
     * Execute a callable with the given overrides as parameters. Retrieve the
     * remaining parameters from the container.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @return mixed
     */
    public function call(callable $callable, array $overrides)
    {
        if (is_string($callable) and strpos($callable, '::') !== false) {

            $callable = explode('::', $callable);

        }

        $reflection = is_array($callable)
            ? new ReflectionMethod($callable[0], $callable[1])
            : new Reflectionfunction($callable);

        $parameters = $reflection->getParameters();

        $values = $this->getResolvedParameters($parameters, $overrides);

        if (! is_array($callable)) {

            return $reflection->invokeArgs($values);

        }

        $instance = ! $reflection->isStatic() ? $callable[0] : null;

        return $reflection->invokeArgs($instance, $values);
    }

    /**
     * Return the values which have a named key.
     *
     * @param array $overrides
     * @return array
     */
    private function getNamedOverrides(array $overrides): array
    {
        return array_filter($overrides, function ($key) {

            return is_string($key);

        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Return the values which doesn't have a named key.
     *
     * @param array $overrides
     * @return array
     */
    private function getDefaultsOverrides(array $overrides): array
    {
        return array_filter($overrides, function ($key) {

            return ! is_string($key);

        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Resolve the list of values to use as parameters from the given list of
     * reflection parameters and the given overrides.
     *
     * @param array $parameters
     * @param array $overrides
     * @return array
     * @throws \Ellipse\Container\Exceptions\NoValueDefinedForParameterException
     */
    private function getResolvedParameters(array $parameters, array $overrides): array
    {
        $named = $this->getNamedOverrides($overrides);
        $defaults = $this->getDefaultsOverrides($overrides);

        return array_map(function (ReflectionParameter $parameter) use ($named, &$defaults) {

            $name = $parameter->getName();

            // priority to overrides having the same name as the parameter.
            if (array_key_exists($name, $named)) {

                return $named[$name];

            }

            // then when the parameter is type hinted as a class try to return
            // an override named as this class. Retrieve an instance of this
            // class from the container when there is no suitable override.
            if ($class = $parameter->getClass()) {

                $name = $class->getName();

                if (array_key_exists($name, $named)) return $named[$name];

                return $this->container->get($name);

            }

            // then try to get a not null default overrides.
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
