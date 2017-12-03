<?php declare(strict_types=1);

namespace Ellipse\Container\Executions;

use ReflectionParameter;

interface ExecutionInterface
{
    /**
     * Return the value of the given factory by resolving the value of the given
     * reflection parameter with the given tail and placeholders.
     *
     * @param callable              $factory
     * @param \ReflectionParameter  $parameter
     * @param array                 $tail
     * @param array                 $placeholders
     * @return mixed
     */
    public function __invoke(callable $factory, ReflectionParameter $parameter, array $tail, array $placeholders);
}
