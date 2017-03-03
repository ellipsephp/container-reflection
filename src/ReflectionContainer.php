<?php declare(strict_types=1);

namespace Pmall\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

use Acclimate\Container\Decorator\AbstractContainerDecorator;

use Pmall\Container\Exceptions\NoValueDefinedForParameterException;

class ReflectionContainer extends AbstractContainerDecorator
{
    public function make(string $class, array $overrides)
    {
        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        $parameters = $constructor->getParameters();

        $values = $this->getResolvedParameters($parameters, $overrides);

        return $reflection->newInstanceArgs($values);
    }

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

    private function getNamedOverrides(array $overrides)
    {
        return array_filter($overrides, function ($key) {

            return is_string($key);

        }, ARRAY_FILTER_USE_KEY);
    }

    private function getDefaultsOverrides(array $overrides)
    {
        return array_filter($overrides, function ($key) {

            return ! is_string($key);

        }, ARRAY_FILTER_USE_KEY);
    }

    private function getResolvedParameters(array $parameters, array $overrides)
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
