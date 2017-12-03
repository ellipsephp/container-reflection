<?php declare(strict_types=1);

namespace Ellipse\Container\Executions;

use ReflectionParameter;

use Ellipse\Container\Executions\Exceptions\UnresolvedParameterException;

class FaillingExecution implements ExecutionInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(callable $factory, ReflectionParameter $parameter, array $tail, array $placeholders)
    {
        throw new UnresolvedParameterException($parameter);
    }
}
