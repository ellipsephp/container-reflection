<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionClass;

class ReflectedClass
{
    private $reflection;

    public function __construct(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    public function isInstantiable(): bool
    {
        return ! $this->reflection->isInterface()
            && ! $this->reflection->isAbstract();
    }

    public function getParameters(): array
    {
        if ($constructor = $this->reflection->getConstructor()) {

            return $constructor->getParameters();

        }

        return [];
    }
}
