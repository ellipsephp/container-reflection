<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\onStatic;

use Ellipse\Container\Reflector;
use Ellipse\Container\ReflectedClass;

describe('ReflectedClass', function () {

    beforeEach(function () {

        $this->reflector = new Reflector;

    });

    describe('->getReflectedClass()', function () {

        it('should return a ReflectionClass', function () {

            $test = $this->reflector->getReflectedClass(StdClass::class);

            expect($test)->toBeAnInstanceOf(ReflectedClass::class);

        });

    });

    describe('->getReflectedParameters()', function () {

        it('should return the parameters of a function name', function () {

            function test (string $p1, $p2 = 'default') {};

            $test = $this->reflector->getReflectedParameters('test');

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

        it('should return the parameters of an annonymous function', function () {

            $callable = function (string $p1, $p2 = 'default') {};

            $test = $this->reflector->getReflectedParameters($callable);

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

        it('should return the parameters of an object method', function () {

            $object = mock(['method' => function (string $p1, $p2 = 'default') {}])->get();

            $callable = [$object, 'method'];

            $test = $this->reflector->getReflectedParameters($callable);

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

        it('should return the parameters of a class static method', function () {

            $object = onStatic(mock(['static method' => function (string $p1, $p2 = 'default') {}]));

            $callable = [$object->className(), 'method'];

            $test = $this->reflector->getReflectedParameters($callable);

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

        it('should return the parameters of a string representation of a class static method', function () {

            $object = onStatic(mock(['static method' => function (string $p1, $p2 = 'default') {}]));

            $callable = implode('::', [$object->className(), 'method']);

            $test = $this->reflector->getReflectedParameters($callable);

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

        it('should return the parameters of an invokable object', function () {

            $callable = mock(['__invoke' => function (string $p1, $p2 = 'default') {}])->get();

            $test = $this->reflector->getReflectedParameters($callable);

            expect($test)->toBeAn('array');
            expect($test)->toHaveLength(2);
            expect($test[0])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[1])->toBeAnInstanceOf(ReflectionParameter::class);
            expect($test[0]->getType())->toEqual('string');
            expect($test[1]->getDefaultValue())->toEqual('default');

        });

    });

});
