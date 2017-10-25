<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ReflectedParameter;
use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

describe('ReflectedParameter', function () {

    beforeEach(function () {

        $this->reflection = mock(ReflectionParameter::class);

        $this->parameter = new ReflectedParameter($this->reflection->get());

    });

    describe('->getValue()', function () {

        beforeEach(function () {

            $this->container = mock(ReflectionContainer::class);

        });

        context('when the parameter has a class type hint', function () {

            beforeEach(function () {

                $this->class = mock(ReflectionClass::class);

                $this->class->getName->returns('SomeClass');

                $this->reflection->getClass->returns($this->class);

            });

            context('when the parameter is in the override list', function () {

                it('should return the value associated to it', function () {

                    $instance = new class () {};

                    $container = $this->container->get();
                    $overrides = ['SomeClass' => $instance];
                    $defaults = ['d1', 'd2'];

                    $test = $this->parameter->getValue($container, $overrides, $defaults);

                    expect($test)->toEqual([$instance, $defaults]);

                });

            });

            context('when the parameter is not in the override list', function () {

                it('should make the parameter class name using the container', function () {

                    $instance = new class () {};

                    $container = $this->container->get();
                    $overrides = ['SomeOtherClass' => new class () {}];
                    $defaults = ['d1', 'd2'];

                    $this->container->make->with('SomeClass', $overrides)->returns($instance);

                    $test = $this->parameter->getValue($container, $overrides, $defaults);

                    expect($test)->toEqual([$instance, ['d1', 'd2']]);

                });

            });

        });

        context('when the parameter does not have a class type hint', function () {

            beforeEach(function() {

                $this->reflection->getClass->returns(null);

            });

            context('when the default values array is not empty', function () {

                it('should return the first value of the values array', function () {

                    $container = $this->container->get();
                    $overrides = [];
                    $defaults = ['d1', 'd2'];

                    $test = $this->parameter->getValue($container, $overrides, $defaults);

                    expect($test)->toEqual(['d1', ['d2']]);

                });

            });

            context('when the default values array is empty', function () {

                context('when the parameter has a default value', function () {

                    it('should return the parameter default value', function () {

                        $this->reflection->isDefaultValueAvailable->returns(true);
                        $this->reflection->getDefaultValue->returns('value');

                        $container = $this->container->get();

                        $test = $this->parameter->getValue($container);

                        expect($test)->toEqual(['value', []]);

                    });

                    it('should return null if the parameter default value is null', function () {

                        $this->reflection->isDefaultValueAvailable->returns(true);
                        $this->reflection->getDefaultValue->returns(null);

                        $container = $this->container->get();

                        $test = $this->parameter->getValue($container);

                        expect($test)->toEqual([null, []]);

                    });

                });

                context('when the parameter does not have a default value', function () {

                    it('should throw ParameterValueCantBeResolvedException', function () {

                        $this->reflection->isDefaultValueAvailable->returns(false);

                        $container = $this->container->get();

                        $test = function () use ($container) {

                            $this->parameter->getValue($container);

                        };

                        $exception = new ParameterValueCantBeResolvedException($this->reflection->get());

                        expect($test)->toThrow($exception);

                    });

                });

            });

        });

    });

});
