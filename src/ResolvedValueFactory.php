<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

use Ellipse\Container\Executions\ExecutionWithTypeHint;
use Ellipse\Container\Executions\ExecutionWithPlaceholder;
use Ellipse\Container\Executions\ExecutionWithDefaultValue;
use Ellipse\Container\Executions\FaillingExecution;

class ResolvedValueFactory
{
    private $delegate;

    public function __construct(ReflectionContainer $container, array $overrides)
    {
        $this->delegate = new ExecutionWithTypeHint(
            new ExecutionWithOverriddenTypeHint(
                $this,
                $overrides,
                new ExecutionWithContainedTypeHint(
                    $this,
                    $container,
                    new ExecutionWithClassTypeHint(
                        $this,
                        $container,
                        $overrides
                    )
                )
            ),
            new ExecutionWithPlaceholder(
                $this,
                new ExecutionWithDefaultValue(
                    $this,
                    new FaillingExecution
                )
            )
        );
    }

    public function __invoke(callable $factory, array $parameters, array $placeholders)
    {
        if (count($parameters) > 0) {

            $parameter = array_shift($parameters);

            return ($this->delegate)($factory, $parameter, $parameters, $placeholders);

        }

        return $factory();
    }
}
