<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\stub;

use Psr\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Reflector;
use Ellipse\Container\Resolver;
use Ellipse\Container\ReflectedClass;
use Ellipse\Container\Exceptions\ClassNotFoundException;
use Ellipse\Container\Exceptions\ImplementationNotDefinedException;

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->decorated = mock(ContainerInterface::class);
        $this->reflector = mock(Reflector::class);
        $this->resolver = mock(Resolver::class);

        $this->container = new ReflectionContainer(
            $this->decorated->get(),
            $this->reflector->get(),
            $this->resolver->get()
        );

    });

    it('should implements container interface', function () {

        expect($this->container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    describe('::decorate()', function () {

        it('should return a ReflectionContainer', function () {

            $test = ReflectionContainer::decorate($this->decorated->get());

            expect($test)->toBeAnInstanceOf(ReflectionContainer::class);

        });

    });

    describe('->get()', function () {

        it('should proxy the underlying container get method', function () {

            $instance = new class () {};

            $this->decorated->get->with('id')->returns($instance);

            $test = $this->container->get('id');

            expect($test)->toEqual($instance);
            $this->decorated->get->called();

        });

    });

    describe('->has()', function () {

        it('should call the underlying container has method and return true when it succeeded', function () {

            $this->decorated->has->with('id')->returns(true);

            $test = $this->container->has('id');

            expect($test)->toBe(true);
            $this->decorated->has->called();

        });

        it('should call the underlying container has method and return false when it failed', function () {

            $this->decorated->has->with('id')->returns(false);

            $test = $this->container->has('id');

            expect($test)->toBe(false);
            $this->decorated->has->called();

        });

    });

    describe('->make()', function () {

        context('when the id is not an interface or class name', function () {

            it('should fail', function () {

                $test = function () { $this->container->make('id'); };

                expect($test)->toThrow(new ClassNotFoundException('id'));

            });

        });

        context('when the container contains a definition for this id', function () {

            it('should return the instance provided by the container', function () {

                $instance = new class () {};

                $this->decorated->has->with(stdClass::class)->returns(true);
                $this->decorated->get->with(stdClass::class)->returns($instance);

                $test = $this->container->make(stdClass::class);

                expect($test)->toEqual($instance);
                $this->decorated->has->called();
                $this->decorated->get->called();

            });

        });

        context('when the container does not contain a definition for this id', function () {

            beforeEach(function () {

                $this->reflected = mock(ReflectedClass::class);

                $this->decorated->has->with(StdClass::class)->returns(false);

                $this->reflector->getReflectedClass->with(StdClass::class)->returns($this->reflected);

            });

            context('when the id is not the name of an instantiable class', function () {

                beforeEach(function () {

                    $this->reflected->isInstantiable->returns(false);

                });

                it('should throw ImplementationNotDefinedException', function () {

                    $test = function () { $this->container->make(StdClass::class); };

                    expect($test)->toThrow(new ImplementationNotDefinedException(StdClass::class));
                    $this->reflected->isInstantiable->called();

                });

            });

            context('when the id is the name of an instantiable class', function () {

                beforeEach(function () {

                    $this->reflected->isInstantiable->returns(true);

                });

                it('should resolve the class constructor parameters and instantiate the class with those resolved values', function () {

                    $parameters = [
                        mock(ReflectionParameter::class)->get(),
                        mock(ReflectionParameter::class)->get(),
                        mock(ReflectionParameter::class)->get(),
                    ];

                    $overrides = ['Class' => new class () {}];
                    $defaults = ['d1', 'd2', 'd3'];

                    $this->reflected->getParameters->returns($parameters);

                    $this->resolver->map->with(
                        $this->container,
                        $parameters,
                        $overrides,
                        $defaults
                    )->returns(['v1', 'v2', 'v3']);

                    $test = $this->container->make(StdClass::class, $overrides, $defaults);

                    expect($test)->toBeAnInstanceOf(StdClass::class);
                    $this->reflector->getReflectedClass->called();
                    $this->reflected->isInstantiable->called();
                    $this->reflected->getParameters->called();
                    $this->resolver->map->called();

                });

            });

        });

    });

    describe('->call()', function () {

        it('should resolve the callable parameters and call it with the resolved values', function () {

            $instance = new class () {};

            $callable = stub(function () {})->with('v1', 'v2', 'v3')->returns($instance);

            $parameters = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

            $overrides = ['Class' => new class () {}];
            $defaults = ['d1', 'd2', 'd3'];

            $this->reflector->getReflectedParameters->with($callable)
                ->returns($parameters);

            $this->resolver->map->with(
                $this->container,
                $parameters,
                $overrides,
                $defaults
            )->returns(['v1', 'v2', 'v3']);

            $test = $this->container->call($callable, $overrides, $defaults);

            expect($test)->toEqual($instance);
            $this->reflector->getReflectedParameters->called();
            $this->resolver->map->called();
            $callable->called();

        });

    });

});
