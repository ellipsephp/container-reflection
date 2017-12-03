<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\TypeHinted;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;

class ExecutionWithClassTypeHint implements ExecutionWithTypeHintInterface
{
    /**
     * The resolved value factory.
     *
     * @var \Ellipse\Container\ResolvedValueFactory
     */
    private $factory;

    /**
     * The reflection container.
     *
     * @var \Ellipse\Container\ReflectionContainer
     */
    private $container;

    /**
     * The associative array of overrides.
     *
     * @var array
     */
    private $overrides;

    /**
     * Set up an execution with contained type hint using the given resolved
     * value factory, reflection container and overrides.
     *
     * @param \Ellipse\Container\ResolvedValueFactory   $factory
     * @param \Ellipse\Container\ReflectionContainer    $container
     * @param array                                     $overrides
     */
    public function __construct(ResolvedValueFactory $factory, ReflectionContainer $container, array $overrides)
    {
        $this->factory = $factory;
        $this->container = $container;
        $this->overrides = $overrides;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, string $class, array $tail, array $placeholders)
    {
        $value = $this->container->make($class, $this->overrides);

        return ($this->factory)(new PartiallyResolvedValue($factory, $value), $tail, $placeholders);
    }
}
