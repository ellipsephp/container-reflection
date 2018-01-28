<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

use Ellipse\Resolvable\ResolvableClassFactory;
use Ellipse\Resolvable\Classes\ClassReflectionFactory;
use Ellipse\Resolvable\Classes\NotAbstractClassReflectionFactory;

class ReflectionContainer extends AbstractReflectionContainer
{
    /**
     * Set up a reflection container with the given delegate and list of auto
     * wirable interfaces.
     *
     * @param \Psr\Container\ContainerInterface $delegate
     * @param array                             $interfaces
     */
    public function __construct(ContainerInterface $delegate, array $interfaces = [])
    {
        $factory = new ResolvableClassFactory(
            new NotAbstractClassReflectionFactory(
                new ClassReflectionFactory
            )
        );

        parent::__construct($delegate, $factory, $interfaces);
    }
}
