<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Resolvable\ResolvableClassFactory;
use Ellipse\Resolvable\Classes\Exceptions\ClassNotFoundException;

class ReflectionContainer implements ContainerInterface
{
    /**
     * The delegate.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $delegate;

    /**
     * The resolvable class factory.
     *
     * @var \Ellipse\Resolvable\ResolvableClassFactory
     */
    private $factory;

    /**
     * The resolved instances cache.
     *
     * @var array
     */
    private $instances;

    /**
     * Set up a reflection container with the given delegate.
     *
     * @param \Psr\Container\ContainerInterface $delegate
     */
    public function __construct(ContainerInterface $delegate)
    {
        $this->delegate = $delegate;
        $this->factory = new ResolvableClassFactory;
        $this->instances = [];
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        try {

            return $this->delegate->get($id);

        }

        catch (NotFoundExceptionInterface $e) {

            return $this->make($id, $e);

        }
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->delegate->has($id) ?: class_exists($id);
    }

    /**
     * Return an instance of the given class name. When not an existing class,
     * propagate the original not found exception. Cache the created instance so
     * the same one is returned on multiple calls.
     *
     * @param string                                        $class
     * @param \Psr\Container\NeotFoundExceptionInterface    $notfound
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function make(string $id, NotFoundExceptionInterface $notfound)
    {
        if (! array_key_exists($id, $this->instances)) {

            try {

                return $this->instances[$id] = ($this->factory)($id)->value($this);

            }

            catch (ClassNotFoundException $e) {

                throw $notfound;

            }

        }

        return $this->instances[$id];
    }
}
