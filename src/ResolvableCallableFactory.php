<?php declare(strict_types=1);

namespace Ellipse\Container;

use Ellipse\Container\Callables\ClosureReflectionFactory;
use Ellipse\Container\Callables\InvokableObjectReflectionFactory;
use Ellipse\Container\Callables\MethodArrayReflectionFactory;
use Ellipse\Container\Callables\MethodStringReflectionFactory;
use Ellipse\Container\Callables\FunctionNameReflectionFactory;
use Ellipse\Container\Callables\FaillingCallableReflectionFactory;

class ResolvableCallableFactory
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Callables\CallableReflectionFactoryInterface
     */
    private $delegate;

    /**
     * Set up a resolvable callable factory.
     */
    public function __construct()
    {
        $this->delegate = new ClosureReflectionFactory(
            new InvokableObjectReflectionFactory(
                new MethodArrayReflectionFactory(
                    new MethodStringReflectionFactory(
                        new FunctionNameReflectionFactory(
                            new FaillingCallableReflectionFactory
                        )
                    )
                )
            )
        );
    }

    /**
     * Return a new ResolvableValue from the given callable.
     *
     * @param callable $callable
     * @return \Ellipse\Container\ResolvableValue
     */
    public function __invoke(callable $callable): ResolvableValue
    {
        $reflection = ($this->delegate)($callable);

        $parameters = $reflection->getParameters();

        return new ResolvableValue($callable, $parameters);
    }
}
