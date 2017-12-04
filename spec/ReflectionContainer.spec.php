<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvableClassFactory;
use Ellipse\Container\ResolvableCallableFactory;
use Ellipse\Container\ResolvableValue;
use Ellipse\Container\Exceptions\ClassInstantiationException;
use Ellipse\Container\Exceptions\CallableExecutionException;
use Ellipse\Container\Exceptions\UnresolvedParameterException;

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

            beforeEach(function () {

                $this->delegate->has->with('class')->returns(false);

                $this->overrides = ['overridden' => new class () {}];
                $this->placeholders = ['p1', 'p2'];

                $this->resolvable = mock(ResolvableValue::class);

                $this->class->__invoke->with('class')->returns($this->resolvable);

            });

            context('when no UnresolvedParameterException is thrown', function () {

                it('should use the resolvable class factory to produce a resolvable value and proxy its ->value() method', function () {

                    $instance = new class () {};

                    $this->resolvable->value
                        ->with($this->container, $this->overrides, $this->placeholders)
                        ->returns($instance);

                    $test = $this->container->make('class', $this->overrides, $this->placeholders);

                    expect($test)->toBe($instance);

                });

            });

            context('when a UnresolvedParameterException is thrown', function () {

                it('should wrap it into a ClassInstantiationException', function () {

                    $exception = mock(UnresolvedParameterException::class)->get();

                    $this->resolvable->value
                        ->with($this->container, $this->overrides, $this->placeholders)
                        ->throws($exception);

                    $test = function () {

                        $this->container->make('class', $this->overrides, $this->placeholders);

                    };

                    $exception = new ClassInstantiationException('class', $exception);

                    expect($test)->toThrow($exception);

                });

            });

        });

    });

    describe('->call()', function () {

        beforeEach(function () {

            $this->overrides = ['overridden' => new class () {}];
            $this->placeholders = ['p1', 'p2'];

            $this->resolvable = mock(ResolvableValue::class);

        });

        context('when no UnresolvedParameterException is thrown', function () {

            it('should use the resolvable callable factory to produce a resolvable value and proxy its ->value() method', function () {

                $callable = stub();

                $this->callable->__invoke->with($callable)->returns($this->resolvable);

                $this->resolvable->value
                    ->with($this->container, $this->overrides, $this->placeholders)
                    ->returns('value');

                $test = $this->container->call($callable, $this->overrides, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

        context('when a UnresolvedParameterException is thrown', function () {

            it('should wrap it into a CallableExecutionException', function () {

                $callable = stub();

                $exception = mock(UnresolvedParameterException::class)->get();

                $this->callable->__invoke->with($callable)->returns($this->resolvable);

                $this->resolvable->value
                    ->with($this->container, $this->overrides, $this->placeholders)
                    ->throws($exception);

                $test = function () use ($callable) {

                    $this->container->call($callable, $this->overrides, $this->placeholders);

                };

                $exception = new CallableExecutionException($callable, $exception);

                expect($test)->toThrow($exception);

            });

        });

    });

});

describe('ReflectionContainer', function () {

    beforeAll(function () {

        class TestClass1 {}

        class TestClass2 {

            public function __construct(TestClass3 $class) {}

        }

        class TestClass3 {}

    });

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class);

        $this->reflection = new ReflectionContainer($this->container->get());

    });

    describe('->make()', function () {

        it('should create an instance of the class with the resolved parameters', function () {

            $test = $this->reflection->make(TestClass2::class);

            expect($test)->toBeAnInstanceOf(TestClass2::class);

        });

    });

    describe('->call()', function () {

        it('should call the given callable with the resolved parameters', function () {

            $callable = function (TestClass1 $p1, TestClass2 $p2, int $p3 = 0, int $p4, array $p5 = []) {

                return [$p1, $p2, $p3, $p4, $p5];

            };

            $instance = new TestClass1;

            $test = $this->reflection->call($callable, [TestClass1::class => $instance], [1, 2]);

            expect($test[0])->toBe($instance);
            expect($test[1])->toBeAnInstanceOf(TestClass2::class);
            expect($test[2])->toEqual(1);
            expect($test[3])->toEqual(2);
            expect($test[4])->toEqual([]);

        });

    });

});
