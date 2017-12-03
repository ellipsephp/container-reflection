<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\Callables\InvokableObjectReflectionFactory;
use Ellipse\Container\Callables\CallableReflectionFactoryInterface;

describe('InvokableObjectReflectionFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(CallableReflectionFactoryInterface::class);

        $this->factory = new InvokableObjectReflectionFactory($this->delegate->get());

    });

    it('should implement CallableReflectionFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(CallableReflectionFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        context('when the given callable is an invokable object', function () {

            it('should return an instance of ReflectionMethod', function () {

                $callable = mock(['__invoke' => function () {}])->get();

                $test = ($this->factory)($callable);

                $reflection = new ReflectionMethod($callable, '__invoke');

                expect($test)->toEqual($reflection);

            });

        });

        context('when the given callable is not an invokable object', function () {

            it('should proxy the delegate ->__invoke() method', function () {

                $callable = function () {};

                $reflection = mock(ReflectionFunctionAbstract::class)->get();

                $this->delegate->__invoke->with($callable)->returns($reflection);

                $test = ($this->factory)($callable);

                expect($test)->toBe($reflection);

            });

        });

    });

});
