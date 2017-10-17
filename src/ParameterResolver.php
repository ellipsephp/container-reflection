<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionParameter;

use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

class ParameterResolver
{
    /**
     * Return an array containing the resolved value and the new defaults array.
     *
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param \ReflectionParameter                      $parameter
     * @param array                                     $overrides
     * @param array                                     $defaults
     * @return array
     */
    public function resolve(
        ReflectionContainer $container,
        ReflectionParameter $parameter,
        array $overrides = [],
        array $defaults = []
    ): array {
        // when the parameter is type hinted as a class retrun either its
        // overrided value or an instance made through the container.
        if ($class = $parameter->getClass()) {

            $name = $class->getName();

            $value = $overrides[$name] ?? $container->make($name, $overrides);

        }

        // when there is default values, extract the first one and return it.
        elseif (count($defaults) > 0) {

            $value = array_shift($defaults);

        }

        // when the parameter has a default value return it.
        elseif ($parameter->isDefaultValueAvailable()) {

            $value = $parameter->getDefaultValue();

        }

        // when a value has been resolved return it with the new defaults.
        if (isset($value)) return [$value, $defaults];

        // fail when no value has been resolved.
        throw new ParameterValueCantBeResolvedException($parameter);
    }
}
