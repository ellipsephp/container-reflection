<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectedClass;

describe('ReflectedClass', function () {

    beforeEach(function () {

        $this->reflection = mock(ReflectionClass::class);

        $this->reflected = new ReflectedClass($this->reflection->get());

    });

    describe('->isInstantiable()', function () {

        context('when the class is an interface', function () {

            it('should return false', function () {

                $this->reflection->isInterface->returns(true);
                $this->reflection->isAbstract->returns(false);

                $test = $this->reflected->isInstantiable();

                expect($test)->toBe(false);
                $this->reflection->isInterface->called();

            });

        });

        context('when the class is an abstract class', function () {

            it('should return false', function () {

                $this->reflection->isInterface->returns(false);
                $this->reflection->isAbstract->returns(true);

                $test = $this->reflected->isInstantiable();

                expect($test)->toBe(false);
                $this->reflection->isAbstract->called();

            });

        });

        context('when the class is not an interface or an abstract class', function () {

            it('should return true', function () {

                $this->reflection->isInterface->returns(false);
                $this->reflection->isAbstract->returns(false);

                $test = $this->reflected->isInstantiable();

                expect($test)->toBe(true);
                $this->reflection->isInterface->called();
                $this->reflection->isAbstract->called();

            });

        });

    });

    describe('->getParameters()', function () {

        context('when the class does not have a constructor', function () {

            it('should return an empty array', function () {

                $this->reflection->getConstructor->returns(null);

                $test = $this->reflected->getParameters();

                expect($test)->toEqual([]);
                $this->reflection->getConstructor->called();

            });

        });

        context('when the class have a constructor', function () {

            it('should return the constructor parameters', function () {

                $parameters = [
                    mock(ReflectionParameter::class)->get(),
                    mock(ReflectionParameter::class)->get(),
                    mock(ReflectionParameter::class)->get(),
                ];

                $constructor = mock(ReflectionMethod::class);

                $constructor->getParameters->returns($parameters);

                $this->reflection->getConstructor->returns($constructor);

                $test = $this->reflected->getParameters();

                expect($test)->toEqual($parameters);
                $this->reflection->getConstructor->called();
                $constructor->getParameters->called();

            });

        });

    });

});
