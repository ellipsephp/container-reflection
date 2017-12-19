<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Exceptions\ReflectionContainerException;

use Ellipse\Resolvable\ResolvableClassFactory;
use Ellipse\Resolvable\ResolvableClass;
use Ellipse\Resolvable\Exceptions\ResolvingExceptionInterface;
use Ellipse\Resolvable\Classes\Exceptions\ClassNotFoundException;
use Ellipse\Resolvable\Classes\Exceptions\InterfaceNameException;

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);
        $this->factory = mock(ResolvableClassFactory::class);

        allow(ResolvableClassFactory::class)->toBe($this->factory->get());

        $this->container = new ReflectionContainer($this->delegate->get());

    });

    it('should implement ContainerInterface', function () {

        expect($this->container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    describe('->get()', function () {

        context('when the delegate ->get() method does not throw an NotFoundExceptionInterface', function () {

            it('should proxy the underlying container ->get() method', function () {

                $instance = new class () {};

                $this->delegate->get->with('id')->returns($instance);

                $test = $this->container->get('id');

                expect($test)->toBe($instance);

            });

        });

        context('when the delegate ->get() method throws an NotFoundExceptionInterface', function () {

            beforeEach(function () {

                $this->notfound = mock([Throwable::class, NotFoundExceptionInterface::class])->get();

                $this->delegate->get->with('id')->throws($this->notfound);

            });

            context('when the resolvable class factory does not throw an exception', function () {

                beforeEach(function () {

                    $this->instance1 = new class {};
                    $this->instance2 = new class {};

                    $resolvable = mock(ResolvableClass::class);

                    $this->factory->__invoke->with('id')->returns($resolvable);

                    $resolvable->value->with($this->container)->returns($this->instance1, $this->instance2);

                });

                it('should return an instance of the resolved class', function () {

                    $test = $this->container->get('id');

                    expect($test)->toBe($this->instance1);

                });

                it('should return the same instance when called multiple times', function () {

                    $test1 = $this->container->get('id');
                    $test2 = $this->container->get('id');

                    expect($test1)->toBe($test2);

                });

            });

            context('when the resolvable class factory throws an exception', function () {

                beforeEach(function () {

                    $this->resolvable = mock(ResolvableClass::class);

                    $this->factory->__invoke->with('id')->returns($this->resolvable);

                    $this->test = function () {

                        $this->container->get('id');

                    };

                });

                context('when the resolvable class factory throws a ClassNotFoundException', function () {

                    it('should throw the original not found exception', function () {

                        $exception = mock(ClassNotFoundException::class);

                        $this->resolvable->value->with($this->container)->throws($exception);

                        expect($this->test)->toThrow($this->notfound);

                    });

                });

                context('when the resolvable class factory throws a InterfaceNameException', function () {

                    it('should throw the original not found exception', function () {

                        $exception = mock(InterfaceNameException::class);

                        $this->resolvable->value->with($this->container)->throws($exception);

                        expect($this->test)->toThrow($this->notfound);

                    });

                });

                context('when the resolvable class factory throws a ResolvingExceptionInterface', function () {

                    it('should be wrapped inside a ReflectionContainerException', function () {

                        $exception = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

                        $this->resolvable->value->with($this->container)->throws($exception);

                        $exception = new ReflectionContainerException('id', $exception);

                        expect($this->test)->toThrow($exception);

                    });

                });

            });

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

            context('when the given id is an existing class name', function () {

                it('should return true', function () {

                    $this->delegate->has->with(StdClass::class)->returns(false);

                    $test = $this->container->has(StdClass::class);

                    expect($test)->toBeTruthy();

                });

            });

            context('when the given id is not an existing class name', function () {

                it('should return false', function () {

                    $this->delegate->has->with('id')->returns(false);

                    $test = $this->container->has('id');

                    expect($test)->toBeFalsy();

                });

            });

        });

    });

});

describe('ReflectionContainer', function () {

    beforeAll(function () {

        class TestClass1
        {
            public function __construct(TestClass2 $p) { $this->p1 = $p; }
        }

        class TestClass2
        {
            public function __construct(TestClass3 $p) { $this->p2 = $p; }
        }

        class TestClass3
        {
            public function __construct() {}
        }

    });

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);

        $this->container = new ReflectionContainer($this->delegate->get());

    });

    describe('->has()', function () {

        it('should return true for an existing class', function () {

            $this->delegate->has->with(TestClass1::class)->returns(false);

            $test = $this->container->has(TestClass1::class);

            expect($test)->toBeTruthy();

        });

    });

    describe('->get()', function () {

        it('should return an instance of the given class name', function () {

            $instance = new TestClass3;

            $exception = mock([Throwable::class, NotFoundExceptionInterface::class])->get();

            $this->delegate->get->with(TestClass1::class)->throws($exception);
            $this->delegate->get->with(TestClass2::class)->throws($exception);
            $this->delegate->get->with(TestClass3::class)->returns($instance);

            $test = $this->container->get(TestClass1::class);

            expect($test)->toBeAnInstanceOf(TestClass1::class);
            expect($test->p1->p2)->toBe($instance);

        });

    });

});
