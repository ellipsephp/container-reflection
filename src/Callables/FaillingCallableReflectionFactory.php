<?php declare(strict_types=1);

namespace Ellipse\Container\Callables;

use ReflectionFunctionAbstract;

use Ellipse\Container\Callables\Exceptions\CallableFormatNotSupportedException;

class FaillingCallableReflectionFactory implements CallableReflectionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(callable $callable): ReflectionFunctionAbstract
    {
        throw new CallableFormatNotSupportedException($callable);
    }
}
