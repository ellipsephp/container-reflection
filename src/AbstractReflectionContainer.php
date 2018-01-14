<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Resolvable\ResolvableClassFactoryInterface;
use Ellipse\Resolvable\Exceptions\ResolvingExceptionInterface;

use Ellipse\Container\Exceptions\ReflectionContainerException;

abstract class AbstractReflectionContainer implements ContainerInterface
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
     * @var \Ellipse\Resolvable\ResolvableClassFactoryInterface
     */
    private $factory;

    /**
     * The list of interfaces implemented by the autowirable classes. Empty
     * array means all classes are autowirable.
     *
     * @var array
     */
    private $interfaces;

    /**
     * The associative array of class name => instance pairs. Used as a cache so
     * the reflection container returns the same instance when called multiple
     * times with the same class name.
     *
     * @var array
     */
    private $instances = [];

    /**
     * Set up a reflection container with the given delegate, resolvable class
     * factory and list of interfaces.
     *
     * @param \Psr\Container\ContainerInterface                     $delegate
     * @param \Ellipse\Resolvable\ResolvableClassFactoryInterface   $factory
     * @param array                                                 $interfaces
     */
    public function __construct(ContainerInterface $delegate, ResolvableClassFactoryInterface $factory, array $interfaces  = [])
    {
        $this->delegate = $delegate;
        $this->factory = $factory;
        $this->interfaces = $interfaces;
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

            if ($this->isAutoWirable($id)) {

                return $this->make($id);

            }

            throw $e;

        }
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->delegate->has($id) ?: $this->isAutoWirable($id);
    }

    /**
     * Return whether the given id is an auto wirable class.
     *
     * @param $id
     * @return bool
     */
    private function isAutoWirable($id): bool
    {
        if (is_string($id) && class_exists($id)) {

            if (count($this->interfaces) > 0) {

                return (bool) array_intersect($this->interfaces, class_implements($id));

            }

            return true;

        }

        return false;
    }

    /**
     * Return an instance of the given class name. Cache the created instance so
     * the same one is returned on multiple calls.
     *
     * @param string $class
     * @return mixed
     * @throws \Ellipse\Container\Exceptions\ReflectionContainerException
     */
    private function make(string $class)
    {
        if (! array_key_exists($class, $this->instances)) {

            try {

                return $this->instances[$class] = ($this->factory)($class)->value($this);

            }

            catch (ResolvingExceptionInterface $e) {

                throw new ReflectionContainerException($class, $e);

            }

        }

        return $this->instances[$class];
    }
}
