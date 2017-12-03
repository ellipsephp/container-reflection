<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\TypeHinted;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;

class ExecutionWithContainedTypeHint implements ExecutionWithTypeHintInterface
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
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface
     */
    private $delegate;

    /**
     * Set up an execution with contained type hint using the given resolved
     * value factory, reflection container and delegate.
     *
     * @param \Ellipse\Container\ResolvedValueFactory                                   $factory
     * @param \Ellipse\Container\ReflectionContainer                                    $container
     * @param \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface   $delegate
     */
    public function __construct(ResolvedValueFactory $factory, ReflectionContainer $container, ExecutionWithTypeHintInterface $delegate)
    {
        $this->factory = $factory;
        $this->container = $container;
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, string $class, array $tail, array $placeholders)
    {
        if ($this->container->has($class)) {

            $value = $this->container->get($class);

            return ($this->factory)(new PartiallyResolvedValue($factory, $value), $tail, $placeholders);

        }

        return ($this->delegate)($factory, $class, $tail, $placeholders);
    }
}
