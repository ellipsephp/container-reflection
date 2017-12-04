<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Executions\ExecutionWithTypeHint;
use Ellipse\Container\Executions\ExecutionWithPlaceholder;
use Ellipse\Container\Executions\ExecutionWithDefaultValue;
use Ellipse\Container\Executions\FaillingExecution;
use Ellipse\Container\Exceptions\UnresolvedParameterException;

class ResolvedValueFactory
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Executions\ExecutionInterface;
     */
    private $delegate;

    /**
     * Set up a resolved value factory with the given container and associative
     * array of overridden class instances.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param array                             $overrides
     */
    public function __construct(ReflectionContainer $container, array $overrides)
    {
        $this->delegate = new ExecutionWithTypeHint(
            $this,
            $container,
            $overrides,
            new ExecutionWithPlaceholder(
                $this,
                new ExecutionWithDefaultValue(
                    $this,
                    new FaillingExecution
                )
            )
        );
    }

    /**
     * Execute the given factory by progressively resolving the given parameters
     * eventually using the given placeholders.
     *
     * @param callable  $factory
     * @param array     $parameters
     * @param array     $placeholders
     * @return mixed
     * @throws \Ellipse\Container\Exceptions\UnresolvedParameterException
     */
    public function __invoke(callable $factory, array $parameters, array $placeholders)
    {
        if (count($parameters) > 0) {

            $parameter = array_shift($parameters);

            try {

                return ($this->delegate)($factory, $parameter, $parameters, $placeholders);

            }

            catch (UnresolvedParameterException $e) {

                throw $e;

            }

            catch (ContainerExceptionInterface $e) {

                throw new UnresolvedParameterException($parameter, $e);

            }

        }

        return $factory();
    }
}
