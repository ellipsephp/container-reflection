<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\Callables\ClosureReflectionFactory;
use Ellipse\Container\Callables\CallableReflectionFactoryInterface;

describe('ClosureReflectionFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(CallableReflectionFactoryInterface::class);

        $this->factory = new ClosureReflectionFactory($this->delegate->get());

    });

    it('should implement CallableReflectionFactoryInterface', function () {

        expect($this->factory)->toBeAnInstanceOf(CallableReflectionFactoryInterface::class);

    });

    describe('->__invoke()', function () {

        context('when the given callable is a closure', function () {

            it('should return an instance of ReflectionFunction', function () {

                $callable = function () {};

                $test = ($this->factory)($callable);

                $reflection = new ReflectionFunction($callable);

                expect($test)->toEqual($reflection);

            });

        });

        context('when the given callable is not a closure', function () {

            it('should proxy the delegate ->__invoke() method', function () {

                $callable = stub();

                $reflection = mock(ReflectionFunctionAbstract::class)->get();

                $this->delegate->__invoke->with($callable)->returns($reflection);

                $test = ($this->factory)($callable);

                expect($test)->toBe($reflection);

            });

        });

    });

});
