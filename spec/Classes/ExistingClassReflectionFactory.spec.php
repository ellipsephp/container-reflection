<?php

use Ellipse\Container\Classes\ExistingClassReflectionFactory;
use Ellipse\Container\Classes\ClassReflectionFactoryInterface;
use Ellipse\Container\Classes\Exceptions\ClassNotFoundException;

describe('ExistingClassReflectionFactory', function () {

    beforeEach(function () {

        $this->factory = new ExistingClassReflectionFactory;

    });

    it('should implement ClassReflectionFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(ClassReflectionFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        context('when the given class name exists', function () {

            it('should return a new ReflectionClass', function () {

                $test = ($this->factory)(StdClass::class);

                $reflection = new ReflectionClass(StdClass::class);

                expect($test)->toEqual($reflection);

            });

        });

        context('when the given class name does not exist', function () {

            it('should throw a ClassNotFoundException', function () {

                $test = function () {

                    ($this->factory)('NotExisting');

                };

                $exception = new ClassNotFoundException('NotExisting');

                expect($test)->toThrow($exception);

            });

        });

    });

});
