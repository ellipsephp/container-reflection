<?php declare(strict_types=1);

namespace Ellipse\Container;

class Resolver
{
    /**
     * Return an array of values for the given array of reflected parameters.
     *
     * @param array                                     $parameters
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param array                                     $overrides
     * @param array                                     $defaults
     * @return array
     */
    public function getValues(
        array $parameters,
        ReflectionContainer $container,
        array $overrides = [],
        array $defaults = []
    ): array {
        $values = [];

        foreach ($parameters as $parameter) {

            [$value, $defaults] = $this->getValue($parameter, $container, $overrides, $defaults);

            $values[] = $value;

        }

        return $values;
    }

    /**
    * Return an array containing the resolved values of the given reflected
    * parameter and the new default values array.
     *
     * @param \Ellipse\Container\ReflectedParameter     $parameter
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param array                                     $overrides
     * @param array                                     $defaults
     * @return array
     */
    private function getValue(
        ReflectedParameter $parameter,
        ReflectionContainer $container,
        array $overrides = [],
        array $defaults = []
    ): array {
        return $parameter->getValue($container, $overrides, $defaults);
    }
}
