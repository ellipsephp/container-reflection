<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

use Ellipse\Container\Exceptions\ClassNotFoundException;
use Ellipse\Container\Exceptions\ImplementationNotDefinedException;

class ReflectionContainer implements ContainerInterface
{
    /**
     * The underlying container to decorate.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The reflector.
     *
     * @var \Ellipse\Container\Reflector
     */
    private $reflector;

    /**
     * The resolver.
     *
     * @var \Ellipse\Container\Resolver
     */
    private $resolver;

    /**
     * Return a ReflectionContainer decorating the given container.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return \Ellipse\Container\ReflectionContainer
     */
    public static function decorate(ContainerInterface $container)
    {
        $reflector = new Reflector;
        $resolver = Resolver::getInstance();

        return new ReflectionContainer($container, $reflector, $resolver);
    }

    /**
     * Set up a reflection container with the underlying container to decorate,
     * the given reflector and the given resolver.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Ellipse\Container\Reflector      $reflector
     * @param \Ellipse\Container\Resolver       $resolver
     */
    public function __construct(
        ContainerInterface $container,
        Reflector $reflector,
        Resolver $resolver
    ) {
        $this->container = $container;
        $this->reflector = $reflector;
        $this->resolver = $resolver;
    }

    /**
     * @{inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @{inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Make an instance of the given class with the given overrides and default
     * values.
     *
     * @param string $id
     * @param array  $overrides
     * @param array  $defaults
     * @return mixed
     * @throws Ellipse\Container\Exceptions\ClassNotFoundException
     * @throws Ellipse\Container\Exceptions\ImplementationNotDefinedException
     */
    public function make(string $id, array $overrides = [], array $defaults = [])
    {
        // ensure the id is an interface or class name.
        if (! interface_exists($id) && ! class_exists($id)) {

            throw new ClassNotFoundException($id);

        }

        // if the container contains the service, return the .
        if ($this->has($id)) return $this->get($id);

        // get a reflection of the class.
        $reflected = $this->reflector->getReflectedClass($id);

        // fail when the alias is not instantiable.
        if (! $reflected->isInstantiable()) {

            throw new ImplementationNotDefinedException($id);

        }

        // get the class constructor parameters.
        $parameters = $reflected->getParameters();

        // try to resolve those parameters.
        $values = $this->resolver->map($this, $parameters, $overrides, $defaults);

        // return an instance of the class.
        return new $id(...$values);
    }

    /**
     * Execute a callable with the given overrides as parameters. Retrieve the
     * remaining parameters from the container.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @param array     $values
     * @return mixed
     */
    public function call(callable $callable, array $overrides = [], array $values = [])
    {
        // get a reflection of the class.
        $parameters = $this->reflector->getReflectedParameters($callable);

        // try to resolve those parameters.
        $values = $this->resolver->map($this, $parameters, $overrides, $values);

        // call the callable with those values.
        return $callable(...$values);
    }
}
