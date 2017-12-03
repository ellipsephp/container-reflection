<?php declare(strict_types=1);

namespace Ellipse\Container\Executions;

use ReflectionParameter;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;

class ExecutionWithPlaceholder implements ExecutionInterface
{
    /**
     * The resolved value factory.
     *
     * @var \Ellipse\Container\ResolvedValueFactory
     */
    private $factory;

    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\ExecutionInterface
     */
    private $delegate;

    /**
     * Set up an execution with placeholder with the given resolved value
     * factory and delegate.
     *
     * @param \Ellipse\Container\ResolvedValueFactory           $factory
     * @param \Ellipse\Container\Executions\ExecutionInterface  $delegate
     */
    public function __construct(ResolvedValueFactory $factory, ExecutionInterface $delegate)
    {
        $this->factory = $factory;
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, ReflectionParameter $parameter, array $tail, array $placeholders)
    {
        if (count($placeholders) > 0) {

            $value = array_shift($placeholders);

            return ($this->factory)(new PartiallyResolvedValue($factory, $value), $tail, $placeholders);

        }

        return ($this->delegate)($factory, $parameter, $tail, $placeholders);
    }
}
