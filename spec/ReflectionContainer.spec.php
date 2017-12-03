<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvableClassFactory;
use Ellipse\Container\ResolvableCallableFactory;
use Ellipse\Container\ResolvableValue;

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);
        $this->class = mock(ResolvableClassFactory::class);
        $this->callable = mock(ResolvableCallableFactory::class);

        allow(ResolvableClassFactory::class)->toBe($this->class->get());
        allow(ResolvableCallableFactory::class)->toBe($this->callable->get());

        $this->container = new ReflectionContainer($this->delegate->get());

    });

    it('should implement ContainerInterface', function () {

        expect($this->container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    describe('->get()', function () {

        it('should proxy the underlying container get method', function () {

            $instance = new class () {};

            $this->delegate->get->with('id')->returns($instance);

            $test = $this->container->get('id');

            expect($test)->toBe($instance);

        });

    });

    describe('->has()', function () {

        context('when the delegate ->has() method returns true', function () {

            it('should return true', function () {

                $this->delegate->has->with('id')->returns(true);

                $test = $this->container->has('id');

                expect($test)->toBeTruthy();

            });

        });

        context('when the delegate ->has() method returns false', function () {

            it('should return false', function () {

                $this->delegate->has->with('id')->returns(false);

                $test = $this->container->has('id');

                expect($test)->toBeFalsy();

            });

        });

    });

    describe('->make()', function () {

        context('when the given class name is contained in the delegate', function () {

            it('should proxy the delegate ->get() method', function () {

                $instance = new class () {};

                $this->delegate->has->with('class')->returns(true);
                $this->delegate->get->with('class')->returns($instance);

                $test = $this->container->make('class');

                expect($test)->toBe($instance);

            });

        });

        context('when the given class name is not contained in the delegate', function () {

            it('should use the resolvable class factory to produce a resolvable value and proxy its ->value() method', function () {

                $this->delegate->has->with('class')->returns(false);

                $overrides = ['overridden' => new class () {}];
                $placeholders = ['p1', 'p2'];

                $instance = new class () {};

                $resolvable = mock(ResolvableValue::class);

                $this->class->__invoke->with('class')->returns($resolvable);

                $resolvable->value->with($this->container, $overrides, $placeholders)->returns($instance);

                $test = $this->container->make('class', $overrides, $placeholders);

                expect($test)->toBe($instance);

            });

        });

    });

    describe('->call()', function () {

        it('should use the resolvable callable factory to produce a resolvable value and proxy its->value() method', function () {

            $overrides = ['overridden' => new class () {}];
            $placeholders = ['p1', 'p2'];

            $callable = stub();

            $resolvable = mock(ResolvableValue::class);

            $this->callable->__invoke->with($callable)->returns($resolvable);

            $resolvable->value->with($this->container, $overrides, $placeholders)->returns('value');

            $test = $this->container->call($callable, $overrides, $placeholders);

            expect($test)->toEqual('value');

        });

    });

});
