<?php declare(strict_types=1);

namespace Ellipse\Container;

class PartiallyResolvedValue
{
    private $factory;

    private $value;

    public function __construct(callable $factory, $value)
    {
        $this->factory = $factory;
        $this->value = $value;
    }

    public function __invoke(...$xs)
    {
        return ($this->factory)($this->value, ...$xs);
    }
}
