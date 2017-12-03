<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\Classes\InstantiableClassReflectionFactory;
use Ellipse\Container\Classes\ClassReflectionFactoryInterface;
use Ellipse\Container\Classes\Exceptions\ClassNotInstantiableException;

describe('InstantiableClassReflectionFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(ClassReflectionFactoryInterface::class);

        $this->factory = new InstantiableClassReflectionFactory($this->delegate->get());

    });

    it('should implement ClassReflectionFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(ClassReflectionFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->reflection = mock(ReflectionClass::class);

            $this->delegate->__invoke->with(StdClass::class)->returns($this->reflection);

        });

        context('when the ->isInvokable() method of the reflection produced by the delegate returns true', function () {

            it('should proxy the delegate ->__invoke() method', function () {

                $this->reflection->isInstantiable->returns(true);

                $test = ($this->factory)(StdClass::class);

                expect($test)->toBe($this->reflection->get());

            });

        });

        context('when the ->isInvokable() method of the reflection produced by the delegate returns false', function () {

            it('should throw a ClassNotInstantiableException', function () {

                $this->reflection->isInstantiable->returns(false);

                $test = function () {

                    ($this->factory)(StdClass::class);

                };

                $exception = new ClassNotInstantiableException(StdClass::class);

                expect($test)->toThrow($exception);

            });

        });

    });

});
