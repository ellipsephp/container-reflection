<?php declare(strict_types=1);

namespace Ellipse\Container;

use ReflectionClass;

class ReflectedClass
{
    /**
     * The reflection class.
     *
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * Set up a reflected class from the given reflection class.
     *
     * @param \ReflectionClass $reflection
     */
    public function __construct(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Return whether the class is instantiable (not an interface or an abstract
     * class).
     *
     * @return bool
     */
    public function isInstantiable(): bool
    {
        return ! $this->reflection->isInterface()
            && ! $this->reflection->isAbstract();
    }

    /**
     * Return an array of reflected parameters from the class constructor.
     *
     * @return array
     */
    public function getReflectedParameters(): array
    {
        $reflections = $this->getParameters();

        return array_map(function ($reflection) {

            return new ReflectedParameter($reflection);

        }, $reflections);
    }

    /**
     * Return an array of reflection parameter from the class constructor.
     *
     * @return array
     */
    private function getParameters(): array
    {
        if ($constructor = $this->reflection->getConstructor()) {

            return $constructor->getParameters();

        }

        return [];
    }
}
