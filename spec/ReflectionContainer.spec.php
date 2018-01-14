<?php

use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\onStatic;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Container\AbstractReflectionContainer;
use Ellipse\Container\ReflectionContainer;

describe('ReflectionContainer', function () {

    beforeAll(function () {

        interface TestInterface1 {}
        interface TestInterface2 {}
        abstract class AbstractTest {}

    });

    beforeEach(function () {

        $this->delegate = mock(ContainerInterface::class);

        $this->container = new ReflectionContainer($this->delegate->get());

    });

    it('should extend AbstractReflectionContainer', function () {

        expect($this->container)->toBeAnInstanceOf(AbstractReflectionContainer::class);

    });

    describe('->get()', function () {

        context('when the delegate ->get() method throws an NotFoundExceptionInterface', function () {

            beforeEach(function () {

                $this->notfound = mock([Throwable::class, NotFoundExceptionInterface::class])->get();

            });

            context('when the id is an existing class name', function () {

                context('when the list of autowirable interfaces is empty', function () {

                    context('when the given id is not the name of an abstract class', function () {

                        it('should return an instance of the class', function () {

                            $this->delegate->get->with(StdClass::class)->throws($this->notfound);

                            $test = $this->container->get(StdClass::class);

                            expect($test)->toBeAnInstanceOf(StdClass::class);

                        });

                    });

                    context('when the given id is the name of an abstract class', function () {

                        it('should throw an exception', function () {

                            $this->delegate->get->with(AbstractTest::class)->throws($this->notfound);

                            $test = function () { $this->container->get(AbstractTest::class); };

                            expect($test)->toThrow();

                        });

                    });

                });

                context('when the list of autowirable interfaces is not empty', function () {

                    beforeEach(function () {

                        $this->container = new ReflectionContainer($this->delegate->get(), [
                            TestInterface1::class,
                            TestInterface2::class,
                        ]);

                    });

                    context('when the given id is not the name of a class implementing one of the autowirable interfaces', function () {

                        it('should throw the original NotFoundExceptionInterface', function () {

                            $this->delegate->get->with(StdClass::class)->throws($this->notfound);

                            $test = function () { $this->container->get(StdClass::class); };

                            expect($test)->toThrow($this->notfound);

                        });

                    });

                    context('when the given id is the name of a class implementing one of the autowirable interfaces', function () {

                        context('when the given id is not the name of an abstract class', function () {

                            it('should return an instance of this class', function () {

                                $id = onStatic(mock(TestInterface2::class))->className();

                                $this->delegate->get->with($id)->throws($this->notfound);

                                $test = $this->container->get($id);

                                expect($test)->toBeAnInstanceOf(TestInterface2::class);

                            });

                        });

                        context('when the given id is the name of an abstract class', function () {

                            it('should throw an exception', function () {

                                $this->delegate->get->with(AbstractTest::class)->throws($this->notfound);

                                $test = function () { $this->container->get(AbstractTest::class); };

                                expect($test)->toThrow();

                            });

                        });

                    });

                });

            });

        });

    });

    describe('->has()', function () {

        context('when the id is an existing class name', function () {

            context('when the delegate ->has() method returns false', function () {

                beforeEach(function () {

                    $this->delegate->has->with(StdClass::class)->returns(false);

                });

                context('when the list of autowirable interfaces is empty', function () {

                    it('should return true', function () {

                        $test = $this->container->has(StdClass::class);

                        expect($test)->toBeTruthy();

                    });

                });

                context('when the list of autowirable interfaces is not empty', function () {

                    beforeEach(function () {

                        $this->container = new ReflectionContainer($this->delegate->get(), [
                            TestInterface1::class,
                            TestInterface2::class,
                        ]);

                    });

                    context('when the given id is not the name of a class implementing one of the autowirable interfaces', function () {

                        it('should return false', function () {

                            $test = $this->container->get(StdClass::class);

                            expect($test)->toBeFalsy();

                        });

                    });

                    context('when the given id is the name of a class implementing one of the autowirable interfaces', function () {

                        it('should return true', function () {

                            $id = onStatic(mock(TestInterface2::class))->className();

                            $test = $this->container->has($id);

                            expect($test)->toBeTruthy();

                        });

                    });

                });

            });

        });

    });

});
