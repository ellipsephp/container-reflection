<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\TypeHinted;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;

class ExecutionWithOverriddenTypeHint implements ExecutionWithTypeHintInterface
{
    /**
     * The resolved value factory.
     *
     * @var \Ellipse\Container\ResolvedValueFactory
     */
    private $factory;

    /**
     * The associative array of overrides.
     *
     * @var array
     */
    private $overrides;

    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface
     */
    private $delegate;

    /**
     * Set up an execution with overridden type hint using the given resolved
     * value factory, associative array of overrides and delegate.
     *
     * @param \Ellipse\Container\ResolvedValueFactory                                   $factory
     * @param array                                                                     $overrides
     * @param \Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface   $delegate
     */
    public function __construct(ResolvedValueFactory $factory, array $overrides, ExecutionWithTypeHintInterface $delegate)
    {
        $this->factory = $factory;
        $this->overrides = $overrides;
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, string $class, array $tail, array $placeholders)
    {
        if (array_key_exists($class, $this->overrides)) {

            $value = $this->overrides[$class];

            return ($this->factory)(new PartiallyResolvedValue($factory, $value), $tail, $placeholders);

        }

        return ($this->delegate)($factory, $class, $tail, $placeholders);
    }
}
