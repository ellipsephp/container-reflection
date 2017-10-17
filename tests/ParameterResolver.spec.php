<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ParameterResolver;
use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

describe('ParameterResolver', function () {

    beforeEach(function () {

        $this->resolver = new ParameterResolver;

    });

    describe('->resolve()', function () {

        beforeEach(function () {

            $this->container = mock(ReflectionContainer::class);
            $this->parameter = mock(ReflectionParameter::class);

        });

        context('when the parameter has a class type hint', function () {

            beforeEach(function () {

                $this->class = mock(ReflectionClass::class);

                $this->class->getName->returns('SomeClass');

                $this->parameter->getClass->returns($this->class);

            });

            context('when the parameter is in the override list', function () {

                it('should return the value associated to it', function () {

                    $instance = new class () {};
                    $overrides = ['SomeClass' => $instance];

                    $test = $this->resolver->resolve(
                        $this->container->get(),
                        $this->parameter->get(),
                        $overrides,
                        ['d1', 'd2']
                    );

                    expect($test)->toEqual([$instance, ['d1', 'd2']]);
                    $this->parameter->getClass->called();
                    $this->class->getName->called();

                });

            });

            context('when the parameter is not in the override list', function () {

                it('should make the parameter class name using the container', function () {

                    $instance = new class () {};
                    $overrides = ['SomeOtherClass' => new class () {}];

                    $this->container->make->with('SomeClass', $overrides)->returns($instance);

                    $test = $this->resolver->resolve(
                        $this->container->get(),
                        $this->parameter->get(),
                        $overrides,
                        ['d1', 'd2']
                    );

                    expect($test)->toEqual([$instance, ['d1', 'd2']]);
                    $this->parameter->getClass->called();
                    $this->class->getName->called();
                    $this->container->make->called();

                });

            });

        });

        context('when the parameter does not have a class type hint', function () {

            beforeEach(function() {

                $this->parameter->getClass->returns(null);

            });

            context('when the values array is not empty', function () {

                it('should return the first value of the values array', function () {

                    $test = $this->resolver->resolve(
                        $this->container->get(),
                        $this->parameter->get(),
                        [],
                        ['d1', 'd2']
                    );

                    expect($test)->toEqual(['d1', ['d2']]);
                    $this->parameter->getClass->called();

                });

            });

            context('when the values array is empty', function () {

                context('when the parameter has a default value', function () {

                    it('should return the default value', function () {

                        $this->parameter->isDefaultValueAvailable->returns(true);
                        $this->parameter->getDefaultValue->returns('value');

                        $test = $this->resolver->resolve(
                            $this->container->get(),
                            $this->parameter->get()
                        );

                        expect($test)->toEqual(['value', []]);
                        $this->parameter->getClass->called();
                        $this->parameter->isDefaultValueAvailable->called();
                        $this->parameter->getDefaultValue->called();

                    });

                    it('should return null if the default value is null', function () {

                        $this->parameter->isDefaultValueAvailable->returns(true);
                        $this->parameter->getDefaultValue->returns(null);

                        $test = $this->resolver->resolve(
                            $this->container->get(),
                            $this->parameter->get()
                        );

                        expect($test)->toEqual([null, []]);
                        $this->parameter->getClass->called();
                        $this->parameter->isDefaultValueAvailable->called();
                        $this->parameter->getDefaultValue->called();

                    });

                });

                context('when the parameter does not have a default value', function () {

                    it('should fail', function () {

                        $this->parameter->isDefaultValueAvailable->returns(false);

                        $test = function () {

                            $this->resolver->resolve(
                                $this->container->get(),
                                $this->parameter->get()
                            );

                        };

                        expect($test)->toThrow(new ParameterValueCantBeResolvedException($this->parameter->get()));
                        $this->parameter->getClass->called();
                        $this->parameter->isDefaultValueAvailable->called();

                    });

                });

            });

        });

    });

});
