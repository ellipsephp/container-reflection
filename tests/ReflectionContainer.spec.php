<?php

use Psr\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Exceptions\ClassDoesNotExistException;
use Ellipse\Container\Exceptions\InterfaceImplementationNotProvidedException;
use Ellipse\Container\Exceptions\AbstractClassImplementationNotProvidedException;
use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

interface DummyInterface
{
    //
}

abstract class DummyAbstractClass
{
    //
}

class DummyClassWithoutConstructor
{
    //
}

class DummyClassWithEmptyConstructor
{
    public function __construct()
    {
        //
    }
}

class DummyClass
{
    private $parameters = [];

    public function __construct($arg1, DummyArg1 $arg2, DummyArg2 $arg3, DummyArg3 $arg4, $arg5, $arg6 = 'arg6')
    {
        $this->parameters[] = $arg1;
        $this->parameters[] = $arg2;
        $this->parameters[] = $arg3;
        $this->parameters[] = $arg4;
        $this->parameters[] = $arg5;
        $this->parameters[] = $arg6;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}

class DummyClassStatic
{
    static public function getInstance ($arg1, DummyArg1 $arg2, DummyArg2 $arg3, DummyArg3 $arg4, $arg5, $arg6 = 'arg6')
    {
        return [$arg1, $arg2, $arg3, $arg4, $arg5, $arg6];
    }
}

class DummyArg1
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class DummyArg2
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class DummyArg3
{
    private $container;
    private $arg1;
    private $arg2;

    public function __construct(ContainerInterface $container, DummyArg4 $arg1, DummyArg5 $arg2)
    {
        $this->container = $container;
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getArg1()
    {
        return $this->arg1;
    }

    public function getArg2()
    {
        return $this->arg2;
    }
}

class DummyArg4
{
    //
}

class DummyArg5
{
    //
}

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->wrapped = Mockery::mock(ContainerInterface::class);
        $this->container = new ReflectionContainer($this->wrapped);

    });

    afterEach(function () {

        Mockery::close();

    });

    it('should implements container interface', function () {

        expect($this->container)->to->be->an->instanceof(ContainerInterface::class);

    });

    describe('->get()', function () {

        it('should proxy the underlying container get method', function () {

            $alias = 'test';
            $instance = new class {};

            $this->wrapped->shouldReceive('get')->once()
                ->with($alias)
                ->andReturn($instance);

            $test = $this->container->get($alias);

            expect($test)->to->be->equal($instance);

        });

    });

    describe('->has()', function () {

        it('should call the underlying container has method and return true when it succeeded', function () {

            $alias = 'test';
            $expected = true;

            $this->wrapped->shouldReceive('has')->once()
                ->with($alias)
                ->andReturn($expected);

            $test = $this->container->has($alias);

            expect($test)->to->be->equal($expected);

        });

        it('should call the underlying container has method and return false when it failed', function () {

            $alias = 'test';
            $expected = false;

            $this->wrapped->shouldReceive('has')->once()
                ->with($alias)
                ->andReturn($expected);

            $test = $this->container->has($alias);

            expect($test)->to->be->equal($expected);

        });

    });

    describe('->make()', function () {

        context('when the given alias is an interface name', function () {

            context('and an implementation is provided by the container', function () {

                it('should return the implementation provided by the container', function () {

                    $implementation = new class {};

                    $this->wrapped->shouldReceive('has')->once()
                        ->with(DummyInterface::class)
                        ->andReturn(true);

                    $this->wrapped->shouldReceive('get')->once()
                        ->with(DummyInterface::class)
                        ->andReturn($implementation);

                    $test = $this->container->make(DummyInterface::class);

                    expect($test)->to->be->equal($implementation);

                });

            });

            context('and no implementation is provided by the container', function () {

                it('should fail', function () {

                    $this->wrapped->shouldReceive('has')->once()
                        ->with(DummyInterface::class)
                        ->andReturn(false);

                    expect([$this->container, 'make'])->with(DummyInterface::class)
                        ->to->throw(InterfaceImplementationNotProvidedException::class);

                });

            });

        });

        context('when the alias is a class name', function () {

            context('and the class does not exists', function () {

                it('should fail', function () {

                    expect([$this->container, 'make'])->with('NonExistingClass')
                        ->to->throw(ClassDoesNotExistException::class);

                });

            });

            context('and the class exists', function () {

                context('and an instance is provided by the container', function () {

                    it('should return the instance provided by the container', function () {

                        $instance = new class {};

                        $this->wrapped->shouldReceive('has')->once()
                            ->with(DummyClass::class)
                            ->andReturn(true);

                        $this->wrapped->shouldReceive('get')->once()
                            ->with(DummyClass::class)
                            ->andReturn($instance);

                        $test = $this->container->make(DummyClass::class);

                        expect($test)->to->be->equal($instance);

                    });

                });

                context('and no instance is provided by the container', function () {

                    context('and the class is an abstract class', function () {

                        it('should fail', function () {

                            $this->wrapped->shouldReceive('has')->once()
                                ->with(DummyAbstractClass::class)
                                ->andReturn(false);

                            expect([$this->container, 'make'])->with(DummyAbstractClass::class)
                                ->to->throw(AbstractClassImplementationNotProvidedException::class);

                        });

                    });

                    context('and the class has no constructor', function () {

                        it('should return an instance of the class', function () {

                            $this->wrapped->shouldReceive('has')->once()
                                ->with(DummyClassWithoutConstructor::class)
                                ->andReturn(false);

                            $test = $this->container->make(DummyClassWithoutConstructor::class);

                            expect($test)->to->be->an->instanceof(DummyClassWithoutConstructor::class);

                        });

                    });

                    context('and the class constructor has no parameters', function () {

                        it('should return an instance of the class', function () {

                            $this->wrapped->shouldReceive('has')->once()
                                ->with(DummyClassWithEmptyConstructor::class)
                                ->andReturn(false);

                            $test = $this->container->make(DummyClassWithEmptyConstructor::class);

                            expect($test)->to->be->an->instanceof(DummyClassWithEmptyConstructor::class);

                        });

                    });

                    context('and a constructor parameter value can\'t be resolved', function () {

                        it('should fail', function () {

                            $this->wrapped->shouldReceive('has')->once()
                                ->with(DummyClass::class)
                                ->andReturn(false);

                            expect([$this->container, 'make'])->with(DummyClass::class)
                                ->to->throw(ParameterValueCantBeResolvedException::class);

                        });

                    });

                });

            });

        });

    });

    describe('->call()', function () {

        context('when the callable has no parameter', function () {

            it('should return the value produced by the callable', function () {

                $cb = function () { return 'value'; };

                $test = $this->container->call($cb);

                expect($test)->to->be->equal('value');

            });

        });

        context('when a parameter can\'t be resolved', function () {

            it('should fail', function () {

                $cb = function ($p) {};

                expect([$this->container, 'call'])->with($cb)
                    ->to->throw(ParameterValueCantBeResolvedException::class);

            });

        });

    });

    context('when all the parameters are resolvable', function () {

        beforeEach(function () {

            $this->arg1 = 'arg1';
            $this->arg2 = new DummyArg1('arg2');
            $this->arg3 = new DummyArg2('arg3');
            $this->arg5 = 'arg5';
            $this->arg6 = 'arg6';
            $this->subarg1 = new DummyArg4;

            $this->overrides = [
                DummyArg2::class => $this->arg3,
                DummyArg4::class => $this->subarg1,
            ];

            $this->parameters = [$this->arg1, $this->arg5];

            $this->wrapped->shouldReceive('has')->once()
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')->once()
                ->with(DummyArg1::class)
                ->andReturn($this->arg2);

            $this->wrapped->shouldReceive('has')->once()
                ->with(DummyArg3::class)
                ->andReturn(false);

            $this->wrapped->shouldReceive('has')->once()
                ->with(DummyArg5::class)
                ->andReturn(false);

            $this->expected = [
                $this->arg1, $this->arg2, $this->arg3, $this->arg5, $this->arg6,
            ];

        });

        describe('->make()', function () {

            it('should recursively inject the dependencies in class constructors', function () {

                $this->wrapped->shouldReceive('has')->once()
                    ->with(DummyClass::class)
                    ->andReturn(false);

                $test = $this->container->make(DummyClass::class, $this->overrides, $this->parameters);

                [$p1, $p2, $p3, $p4, $p5, $p6] = $test->getParameters();

                expect($p4)->to->be->an->instanceof(DummyArg3::class);
                expect($p4->getContainer())->to->be->equal($this->wrapped);
                expect($p4->getArg1())->to->be->equal($this->subarg1);
                expect($p4->getArg2())->to->be->an->instanceof(DummyArg5::class);
                expect([$p1, $p2, $p3, $p5, $p6])->to->be->equal($this->expected);

            });

        });

        describe('->call()', function () {

            it('should recursively inject the dependencies of annonymous functions', function () {

                $cb = function ($arg1, DummyArg1 $arg2, DummyArg2 $arg3, DummyArg3 $arg4, $arg5, $arg6 = 'arg6') {

                    return [$arg1, $arg2, $arg3, $arg4, $arg5, $arg6];

                };

                $test = $this->container->call($cb, $this->overrides, $this->parameters);

                [$p1, $p2, $p3, $p4, $p5, $p6] = $test;

                expect($p4)->to->be->an->instanceof(DummyArg3::class);
                expect($p4->getContainer())->to->be->equal($this->wrapped);
                expect($p4->getArg1())->to->be->equal($this->subarg1);
                expect($p4->getArg2())->to->be->an->instanceof(DummyArg5::class);
                expect([$p1, $p2, $p3, $p5, $p6])->to->be->equal($this->expected);

            });

            it('should recursively inject the dependencies of callable arrays representing an instance method', function () {

                $class = new class {

                    public function test ($arg1, DummyArg1 $arg2, DummyArg2 $arg3, DummyArg3 $arg4, $arg5, $arg6 = 'arg6') {

                        return [$arg1, $arg2, $arg3, $arg4, $arg5, $arg6];

                    }

                };

                $cb = [$class, 'test'];

                $test = $this->container->call($cb, $this->overrides, $this->parameters);

                [$p1, $p2, $p3, $p4, $p5, $p6] = $test;

                expect($p4)->to->be->an->instanceof(DummyArg3::class);
                expect($p4->getContainer())->to->be->equal($this->wrapped);
                expect($p4->getArg1())->to->be->equal($this->subarg1);
                expect($p4->getArg2())->to->be->an->instanceof(DummyArg5::class);
                expect([$p1, $p2, $p3, $p5, $p6])->to->be->equal($this->expected);

            });

            it('should recursively inject the dependencies of callable arrays representing a static method', function () {

                $cb = ['DummyClassStatic', 'getInstance'];

                $test = $this->container->call($cb, $this->overrides, $this->parameters);

                [$p1, $p2, $p3, $p4, $p5, $p6] = $test;

                expect($p4)->to->be->an->instanceof(DummyArg3::class);
                expect($p4->getContainer())->to->be->equal($this->wrapped);
                expect($p4->getArg1())->to->be->equal($this->subarg1);
                expect($p4->getArg2())->to->be->an->instanceof(DummyArg5::class);
                expect([$p1, $p2, $p3, $p5, $p6])->to->be->equal($this->expected);

            });

            it('should recursively inject the dependencies of static class method strings', function () {

                $cb = 'DummyClassStatic::getInstance';

                $test = $this->container->call($cb, $this->overrides, $this->parameters);

                [$p1, $p2, $p3, $p4, $p5, $p6] = $test;

                expect($p4)->to->be->an->instanceof(DummyArg3::class);
                expect($p4->getContainer())->to->be->equal($this->wrapped);
                expect($p4->getArg1())->to->be->equal($this->subarg1);
                expect($p4->getArg2())->to->be->an->instanceof(DummyArg5::class);
                expect([$p1, $p2, $p3, $p5, $p6])->to->be->equal($this->expected);

            });

        });

    });

});
