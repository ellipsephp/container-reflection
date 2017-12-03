<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\TypeHinted;

interface ExecutionWithTypeHintInterface
{
    /**
     * Return the value of the given factory by resolving the value of the given
     * class name with the given tail and placeholders.
     *
     * @param callable  $factory
     * @param string    $class
     * @param array     $tail
     * @param array     $placeholders
     * @return mixed
     */
    public function __invoke(callable $factory, string $class, array $tail, array $placeholders);
}
