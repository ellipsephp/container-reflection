<?php declare(strict_types=1);

namespace Ellipse\Container;

use Ellipse\Container\Classes\InstantiableClassReflectionFactory;
use Ellipse\Container\Classes\ExistingClassReflectionFactory;

class ResolvableClassFactory
{
    /**
     * The delegate.
     *
     * @var \Ellipse\Container\Classes\ClassReflectionFactoryInterface
     */
    private $delegate;

    /**
     * Set up a resolvable class factory.
     */
    public function __construct()
    {
        $this->delegate = new InstantiableClassReflectionFactory(
            new ExistingClassReflectionFactory
        );
    }

    /**
     * Return a new ResolvableValue from the given class name.
     *
     * @param string $class
     * @return \Ellipse\Container\ResolvableValue
     */
    public function __invoke(string $class): ResolvableValue
    {
        $reflection = ($this->delegate)($class);

        $constructor = $reflection->getConstructor();

        $factory = [$reflection, 'newInstance'];

        $parameters = is_null($constructor) ? [] : $constructor->getParameters();

        return new ResolvableValue($factory, $parameters);
    }
}
