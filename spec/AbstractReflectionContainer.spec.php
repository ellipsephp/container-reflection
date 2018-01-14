<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\partialMock;
use function Eloquent\Phony\Kahlan\onStatic;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Container\AbstractReflectionContainer;
use Ellipse\Container\Exceptions\ReflectionContainerException;

use Ellipse\Resolvable\ResolvableClass;
use Ellipse\Resolvable\ResolvableClassFactoryInterface;
use Ellipse\Resolvable\Exceptions\ResolvingExceptionInterface;

describe('AbstractReflectionContainer', function () {

    beforeAll(function () {

        interface AbstractTestInterface1 {}
        interface AbstractTestInterface2 {}

    });

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);
        $this->factory = mock(ResolvableClassFactoryInterface::class);

        $this->container = partialMock(AbstractReflectionContainer::class, [
            $this->delegate->get(),
            $this->factory->get(),
            [],
        ])->get();

    });

    it('should implement ContainerInterface', function () {

        expect($this->container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    describe('->get()', function () {

        context('when the delegate ->get() method does not throw an NotFoundExceptionInterface', function () {

            it('should proxy the delegate ->get() method', function () {

                $instance = new class () {};

                $this->delegate->get->with('id')->returns($instance);

                $test = $this->container->get('id');

                expect($test)->toBe($instance);

            });

        });

        context('when the delegate ->get() method throws an NotFoundExceptionInterface', function () {

            beforeEach(function () {

                $this->notfound = mock([Throwable::class, NotFoundExceptionInterface::class])->get();

            });

            context('when the given id is not a string', function () {

                it('should throw the original NotFoundExceptionInterface', function () {

                    $this->delegate->get->with([])->throws($this->notfound);

                    $test = function () { $this->container->get([]); };

                    expect($test)->toThrow($this->notfound);

                });

            });

            context('when the given id is not an existing class name', function () {

                it('should throw the original NotFoundExceptionInterface', function () {

                    $this->delegate->get->with('id')->throws($this->notfound);

                    $test = function () { $this->container->get('id'); };

                    expect($test)->toThrow($this->notfound);

                });

            });

            context('when the given id is an existing class name', function () {

                beforeEach(function () {

                    $this->delegate->get->with(StdClass::class)->throws($this->notfound);

                });

                context('when the list of autowirable interfaces is empty', function () {

                    context('when the resolvable class factory throws a ResolvingExceptionInterface', function () {

                        it('should throw a ReflectionContainerException wrapped around the ResolvingExceptionInterface', function () {

                            $exception = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

                            $this->factory->__invoke->with(StdClass::class)->throws($exception);

                            $test = function () { $this->container->get(StdClass::class); };

                            $exception = new ReflectionContainerException(StdClass::class, $exception);

                            expect($test)->toThrow($exception);

                        });

                    });

                    context('when the resolvable class factory does not throw a ResolvingExceptionInterface', function () {

                        beforeEach(function () {

                            $this->resolvable = mock(ResolvableClass::class);

                            $this->factory->__invoke->with(StdClass::class)->returns($this->resolvable);

                        });

                        context('when the produced resolvable class ->value() method throws a ResolvingExceptionInterface', function () {

                            it('should throw a ReflectionContainerException wrapped around the ResolvingExceptionInterface', function () {

                                $exception = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

                                $this->resolvable->value->with($this->container)->throws($exception);

                                $test = function () { $this->container->get(StdClass::class); };

                                $exception = new ReflectionContainerException(StdClass::class, $exception);

                                expect($test)->toThrow($exception);

                            });

                        });

                        context('when the produced resolvable class ->value() method does not throw a ResolvingExceptionInterface', function () {

                            beforeEach(function () {

                                $this->instance = new class {};

                                $this->resolvable->value->with($this->container)->returns($this->instance);

                            });

                            it('should return the resolved instance', function () {

                                $this->resolvable->value->with(StdClass::class)->returns($this->instance);

                                $test = $this->container->get(StdClass::class);

                                expect($test)->toBe($this->instance);

                            });

                            it('should return the same instance on multiple calls', function () {

                                $test1 = $this->container->get(StdClass::class);
                                $test2 = $this->container->get(StdClass::class);

                                expect($test1)->toBe($test2);

                            });

                        });

                    });

                });

                context('when the list of autowirable interfaces is not empty', function () {

                    beforeEach(function () {

                        $this->container = partialMock(AbstractReflectionContainer::class, [
                            $this->delegate->get(),
                            $this->factory->get(),
                            [AbstractTestInterface1::class, AbstractTestInterface2::class],
                        ])->get();

                    });

                    context('when the given id is not the name of a class implementing one of the autowirable interfaces', function () {

                        it('should throw the original NotFoundExceptionInterface', function () {

                            $test = function () { $this->container->get(StdClass::class); };

                            expect($test)->toThrow($this->notfound);

                        });

                    });

                    context('when the given id is the name of a class implementing one of the autowirable interfaces', function () {

                        beforeEach(function () {

                            $this->id = onStatic(mock(AbstractTestInterface2::class))->className();

                            $this->delegate->get->with($this->id)->throws($this->notfound);

                        });

                        context('when the resolvable class factory throws a ResolvingExceptionInterface', function () {

                            it('should throw a ReflectionContainerException wrapped around the ResolvingExceptionInterface', function () {

                                $exception = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

                                $this->factory->__invoke->with($this->id)->throws($exception);

                                $test = function () { $this->container->get($this->id); };

                                $exception = new ReflectionContainerException($this->id, $exception);

                                expect($test)->toThrow($exception);

                            });

                        });

                        context('when the resolvable class factory does not throw a ResolvingExceptionInterface', function () {

                            beforeEach(function () {

                                $this->resolvable = mock(ResolvableClass::class);

                                $this->factory->__invoke->with($this->id)->returns($this->resolvable);

                            });

                            context('when the produced resolvable class ->value() method throws a ResolvingExceptionInterface', function () {

                                it('should throw a ReflectionContainerException wrapped around the ResolvingExceptionInterface', function () {

                                    $exception = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

                                    $this->resolvable->value->with($this->container)->throws($exception);

                                    $test = function () { $this->container->get($this->id); };

                                    $exception = new ReflectionContainerException($this->id, $exception);

                                    expect($test)->toThrow($exception);

                                });

                            });

                            context('when the produced resolvable class ->value() method does not throw a ResolvingExceptionInterface', function () {

                                beforeEach(function () {

                                    $this->instance = new class {};

                                    $this->resolvable->value->with($this->container)->returns($this->instance);

                                });

                                it('should return the resolved instance', function () {

                                    $this->resolvable->value->with($this->id)->returns($this->instance);

                                    $test = $this->container->get($this->id);

                                    expect($test)->toBe($this->instance);

                                });

                                it('should return the same instance on multiple calls', function () {

                                    $test1 = $this->container->get($this->id);
                                    $test2 = $this->container->get($this->id);

                                    expect($test1)->toBe($test2);

                                });

                            });

                        });

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

            beforeEach(function () {

                $this->delegate->has->with(StdClass::class)->returns(false);

            });

            context('when the given id is not a string', function () {

                it('should return false', function () {

                    $test = $this->container->has([]);

                    expect($test)->toBeFalsy();

                });

            });

            context('when the given id is not an existing class name', function () {

                it('should return false', function () {

                    $test = $this->container->has('id');

                    expect($test)->toBeFalsy();

                });

            });

            context('when the given id is an existing class name', function () {

                context('when the list of autowirable interfaces is empty', function () {

                    it('should return true', function () {

                        $test = $this->container->has(StdClass::class);

                        expect($test)->toBeTruthy();

                    });

                });

                context('when the list of autowirable interfaces is not empty', function () {

                    beforeEach(function () {

                        $this->container = partialMock(AbstractReflectionContainer::class, [
                            $this->delegate->get(),
                            $this->factory->get(),
                            [AbstractTestInterface1::class, AbstractTestInterface2::class],
                        ])->get();

                    });

                    context('when the given id is not the name of a class implementing one of the autowirable interfaces', function () {

                        it('should return true', function () {

                            $test = $this->container->has(StdClass::class);

                            expect($test)->toBeFalsy();

                        });

                    });

                    context('when the given id is the name of a class implementing one of the autowirable interfaces', function () {

                        it('should return true', function () {

                            $id = onStatic(mock(AbstractTestInterface2::class))->className();

                            $test = $this->container->has($id);

                            expect($test)->toBeTruthy();

                        });

                    });

                });

            });

        });

    });

});
