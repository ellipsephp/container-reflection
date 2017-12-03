<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

class ReflectionContainer implements ContainerInterface
{
    /**
     * The underlying container to decorate.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The resolvable class factory.
     *
     * @var \Ellipse\Container\ResolvableClassFactory
     */
    private $class;

    /**
     * The resolvable callable factory.
     *
     * @var \Ellipse\Container\ResolvableCallableFactory
     */
    private $callable;

    /**
     * Set up a reflection container with the given delegate.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->delegate = $container;
        $this->class = new ResolvableClassFactory;
        $this->callable = new ResolvableCallableFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->delegate->get($id);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->delegate->has($id);
    }

    /**
     * Return the instance contained in the delegate when present. Otherwise
     * build a resolvable class and resolve its value with the given overrides
     * and placeholders.
     *
     * @param string    $class
     * @param array     $overrides
     * @param array     $placeholders
     * @return mixed
     */
    public function make(string $class, array $overrides = [], array $placeholders = [])
    {
        if (! $this->delegate->has($class)) {

            return ($this->class)($class)->value($this, $overrides, $placeholders);

        }

        return $this->delegate->get($class);
    }

    /**
     * Return the value produced by the given callable by resolving its
     * parameters using the given overrides and placeholders.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @param array     $placeholders
     * @return mixed
     */
    public function call(callable $callable, array $overrides = [], array $defaults = [])
    {
        return ($this->callable)($callable)->value($this, $overrides, $defaults);
    }
}
