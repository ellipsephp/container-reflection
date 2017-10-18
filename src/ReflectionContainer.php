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
    public static function decorate(ContainerInterface $container): ReflectionContainer
    {
        $reflector = new Reflector;
        $resolver = new Resolver;

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
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Make an instance of the class with the given name using the given
     * overrides and default values.
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
        // ensure the id is an existing interface or class.
        if (! interface_exists($id) && ! class_exists($id)) {

            throw new ClassNotFoundException($id);

        }

        // get the instance provided by the container when it contains the class
        // name.
        if ($this->has($id)) return $this->get($id);

        // get a reflection of the class.
        $reflected = $this->reflector->getReflectedClass($id);

        // fail if the class is not instantiable (interface of abstract class).
        if (! $reflected->isInstantiable()) {

            throw new ImplementationNotDefinedException($id);

        }

        // get the reflected parameters of the class constructor.
        $parameters = $reflected->getReflectedParameters();

        // try to resolve the values of those parameters.
        $values = $this->resolver->getValues($parameters, $this, $overrides, $defaults);

        // instantiate the class with those values and return it.
        return new $id(...$values);
    }

    /**
     * Execute the given callable using the given overrides and default values.
     *
     * @param callable  $callable
     * @param array     $overrides
     * @param array     $defaults
     * @return mixed
     */
    public function call(callable $callable, array $overrides = [], array $defaults = [])
    {
        // get the reflected parameters of the callable.
        $parameters = $this->reflector->getReflectedParameters($callable);

        // try to resolve the values of those parameters.
        $values = $this->resolver->getValues($parameters, $this, $overrides, $defaults);

        // call the callable with those values and return it.
        return $callable(...$values);
    }
}
