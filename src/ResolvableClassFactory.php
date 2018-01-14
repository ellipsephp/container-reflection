<?php declare(strict_types=1);

namespace Ellipse\Container;

use Ellipse\Resolvable\AbstractResolvableClassFactory;
use Ellipse\Resolvable\Classes\ClassReflectionFactory;
use Ellipse\Resolvable\Classes\NotAbstractClassReflectionFactory;

class ResolvableClassFactory extends AbstractResolvableClassFactory
{
    /**
     * Set up a resolvable class factory for existing classes. Fails only when
     * class is abstract.
     */
    public function __construct()
    {
        parent::__construct(new NotAbstractClassReflectionFactory(
            new ClassReflectionFactory
        ));
    }
}
