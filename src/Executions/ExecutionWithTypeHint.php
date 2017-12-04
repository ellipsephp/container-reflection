<?php declare(strict_types=1);

namespace Ellipse\Container\Executions;

use ReflectionParameter;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;

class ExecutionWithTypeHint implements ExecutionInterface
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
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\ExecutionInterface
     */
    private $delegate;

    /**
     * Set up an execution with contained type hint using the given resolved
     * value factory, reflection container, overrides and delegate.
     *
     * @param \Ellipse\Container\ResolvedValueFactory           $factory
     * @param \Ellipse\Container\ReflectionContainer            $container
     * @param array                                             $overrides
     * @param \Ellipse\Container\Executions\ExecutionInterface  $delegate
     */
    public function __construct(
        ResolvedValueFactory $factory,
        ReflectionContainer $container,
        array $overrides,
        ExecutionInterface $delegate
    ) {
        $this->factory = $factory;
        $this->container = $container;
        $this->overrides = $overrides;
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, ReflectionParameter $parameter, array $tail, array $placeholders)
    {
        if ($class = $parameter->getClass()) {

            $name = $class->getName();

            $value = array_key_exists($name, $this->overrides)
                ? $this->overrides[$name]
                : $this->container->make($name, $this->overrides);

            return ($this->factory)(new PartiallyResolvedValue($factory, $value), $tail, $placeholders);

        }

        return ($this->delegate)($factory, $parameter, $tail, $placeholders);
    }
}
