<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionParameter;

use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

class ReflectedParameter
{
    /**
     * The reflection parameter.
     *
     * @var \ReflectionParameter
     */
    private $reflection;

    /**
     * Set up a reflected parameter from the given reflection parameter.
     *
     * @param \ReflectionParameter $reflection
     */
    public function __construct(ReflectionParameter $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Return an array containing the resolved values of this parameter and the
     * new default values array.
     *
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param array                                     $overrides
     * @param array                                     $defaults
     * @return array
     */
    public function getValue(
        ReflectionContainer $container,
        array $overrides = [],
        array $defaults = []
    ): array {
        // when the parameter is type hinted as a class retrun either its
        // overrided value or an instance made through the container.
        if ($class = $this->reflection->getClass()) {

            $name = $class->getName();

            $value = $overrides[$name] ?? $container->make($name, $overrides);

            return [$value, $defaults];

        }

        // when there is default values, extract the first one and return it.
        if (count($defaults) > 0) {

            $value = array_shift($defaults);

            return [$value, $defaults];

        }

        // when the parameter has a default value return it.
        if ($this->reflection->isDefaultValueAvailable()) {

            $value = $this->reflection->getDefaultValue();

            return [$value, $defaults];

        }

        // can't check here if value is set because the default value can be
        // null.

        // fail when no value has been resolved.
        throw new ParameterValueCantBeResolvedException($this->reflection);
    }
}
