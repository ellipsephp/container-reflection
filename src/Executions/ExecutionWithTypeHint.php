<?php declare(strict_types=1);

namespace Ellipse\Container\Executions;

use ReflectionParameter;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface;

class ExecutionWithTypeHint implements ExecutionInterface
{
    /**
     * The type hinted execution.
     *
     * @var \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface
     */
    private $execution;

    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\ExecutionInterface
     */
    private $delegate;

    /**
     * Set up an execution with type hint using the given type hinted execution
     * and delegate.
     *
     * @param \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface   $execution
     * @param \Ellipse\Container\Executions\ExecutionInterface                          $delegate
     */
    public function __construct(ExecutionWithTypeHintInterface $execution, ExecutionInterface $delegate)
    {
        $this->execution = $execution;
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, ReflectionParameter $parameter, array $tail, array $placeholders)
    {
        if ($class = $parameter->getClass()) {

            $name = $class->getName();

            return ($this->execution)($factory, $name, $tail, $placeholders);

        }

        return ($this->delegate)($factory, $parameter, $tail, $placeholders);
    }
}
