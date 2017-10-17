<?php declare(strict_types=1);

namespace Ellipse\Container;

class Resolver
{
    /**
     * The parameter resolver.
     *
     * @var \Ellipse\Container\ParameterResolver
     */
    private $resolver;

    /**
     * Return a Resolver.
     *
     * @return \Ellipse\Container\Resolver
     */
    public static function getInstance(): Resolver
    {
        $resolver = new ParameterResolver;

        return new Resolver($resolver);
    }

    /**
     * Set up a parameter list resolver with the given parameter resolver.
     *
     * @param \Ellipse\Container\ParameterResolver $resolver
     */
    public function __construct(ParameterResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Return an array of values for the given list of reflection parameters.
     *
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param array                                     $parameters
     * @param array                                     $overrides
     * @param array                                     $defaults
     * @return array
     */
    public function map(
        ReflectionContainer $container,
        array $parameters,
        array $overrides = [],
        array $defaults = []
    ): array {
        $values = [];

        foreach ($parameters as $parameter) {

            [$value, $defaults] = $this->resolver->resolve($container, $parameter, $overrides, $defaults);

            $values[] = $value;

        }

        return $values;
    }
}
