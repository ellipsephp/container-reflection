<?php declare(strict_types=1);

namespace Ellipse\Container\Classes;

use ReflectionClass;

use Ellipse\Container\Classes\Exceptions\ClassNotInstantiableException;

class InstantiableClassReflectionFactory implements ClassReflectionFactoryInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Classes\ClassReflectionFactoryInterface
     */
    private $delegate;

    /**
     * Set up an instantiable class resflection factory with the given delegate.
     *
     * @param \Ellipse\Container\Classes\ClassReflectionFactoryInterface $delegate
     */
    public function __construct(ClassReflectionFactoryInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(string $class): ReflectionClass
    {
        $reflection = ($this->delegate)($class);

        if ($reflection->isInstantiable()) {

            return $reflection;

        }

        throw new ClassNotInstantiableException($class);
    }
}
