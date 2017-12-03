<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvableValue;
use Ellipse\Container\ResolvableClassFactory;
use Ellipse\Container\Classes\InstantiableClassReflectionFactory;

describe('ResolvableClassFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(InstantiableClassReflectionFactory::class);

        allow(InstantiableClassReflectionFactory::class)->toBe($this->delegate->get());

        $this->factory = new ResolvableClassFactory;

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->reflection = mock(ReflectionClass::class);

            $this->delegate->__invoke->returns($this->reflection);

            $this->class = [$this->reflection->get(), 'newInstance'];

        });

        context('when the class has a constructor', function () {

            it('should get an array of ReflectionParameter using the reflection class constructor ->getParameters() method', function () {

                $constructor = mock(ReflectionMethod::class);
                $parameters = [
                    mock(ReflectionParameter::class)->get(),
                    mock(ReflectionParameter::class)->get(),
                ];

                $this->reflection->getConstructor->returns($constructor);

                $constructor->getParameters->returns($parameters);

                $test = ($this->factory)('class');

                $resolvable = new ResolvableValue($this->class, $parameters);

                expect($test)->toEqual($resolvable);

            });

        });

        context('when the class dont have a constructor', function () {

            it('should use an empty array of ReflectionParameter', function () {

                $this->reflection->getConstructor->returns(null);

                $test = ($this->factory)('class');

                $resolvable = new ResolvableValue($this->class, []);

                expect($test)->toEqual($resolvable);

            });

        });

    });

});
