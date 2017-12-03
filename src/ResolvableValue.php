<?php declare(strict_types=1);

namespace Ellipse\Container;

class ResolvableValue
{
    private $factory;

    private $parameters;

    public function __construct(callable $factory, array $parameters)
    {
        $this->factory = $factory;
        $this->parameters = $parameters;
    }

    public function __invoke(ReflectionContainer $container, array $overrides, array $placeholders)
    {
        $factory = new ResolvedValueFactory($container, $overrides);

        return $factory($this->factory, $this->parameters, $placeholders);
    }
}
