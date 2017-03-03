<?php

use Interop\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\DefaultValue;
use Ellipse\Container\Exceptions\NoValueDefinedForParameterException;

class DummyArg1
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class DummyArg2
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class DummyClass
{
    private $parameters = [];

    public function __construct(DummyArg1 $arg1, DummyArg2 $arg2, $arg3 = 'arg3', $arg4)
    {
        $this->parameters[] = $arg1;
        $this->parameters[] = $arg2;
        $this->parameters[] = $arg3;
        $this->parameters[] = $arg4;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}

class DummyClassStatic
{
    static public function getInstance(DummyArg1 $arg1, DummyArg2 $arg2, $arg3 = 'arg3', $arg4)
    {
        return new DummyClass($arg1, $arg2, $arg3, $arg4);
    }
}

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->wrapped = Mockery::mock(ContainerInterface::class);
        $this->container = new ReflectionContainer($this->wrapped);

    });

    describe('->make()', function () {

        it('should work as expected', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $test = $this->container->make(DummyClass::class, [
                DummyArg2::class => $arg2,
                new DefaultValue,
                $arg4,
            ]);

            $parameters = $test->getParameters();

            expect($parameters)->to->be->equal([$arg1, $arg2, $arg3, $arg4]);

        });

        it('should fail when it cant resolve one parameter', function () {

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn(new DummyArg1('arg1'));

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg2::class)
                ->andReturn(new DummyArg2('arg2'));

            $test = function ($container) {

                return $container->make(DummyClass::class, []);

            };

            expect($test)->with($this->container)->to->throw(NoValueDefinedForParameterException::class);

        });

    });

    describe('->call()', function () {

        it('should work with annonymous functions', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $cb = function (DummyArg1 $arg1, DummyArg2 $arg2, $arg3 = 'arg3', $arg4) {

                return [$arg1, $arg2, $arg3, $arg4];

            };

            $test = $this->container->call($cb, [
                DummyArg2::class => $arg2,
                new DefaultValue,
                $arg4,
            ]);

            expect($test)->to->be->equal([$arg1, $arg2, $arg3, $arg4]);

        });

        it('should work with class method', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $class = new class {

                public function test (DummyArg1 $arg1, DummyArg2 $arg2, $arg3 = 'arg3', $arg4) {

                    return [$arg1, $arg2, $arg3, $arg4];

                }

            };

            $test = $this->container->call([$class, 'test'], [
                DummyArg2::class => $arg2,
                new DefaultValue,
                $arg4,
            ]);

            expect($test)->to->be->equal([$arg1, $arg2, $arg3, $arg4]);

        });

        it('should work with static class method', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $test = $this->container->call('DummyClassStatic::getInstance', [
                DummyArg2::class => $arg2,
                new DefaultValue,
                $arg4,
            ]);

            $parameters = $test->getParameters();

            expect($parameters)->to->be->equal([$arg1, $arg2, $arg3, $arg4]);

        });

        it('should fail when it cant resolve one parameter', function () {

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn(new DummyArg1('arg1'));

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg2::class)
                ->andReturn(new DummyArg2('arg2'));

            $cb = function (DummyArg1 $arg1, DummyArg2 $arg2, $arg3 = 'arg3', $arg4) {

                return [$arg1, $arg2, $arg3, $arg4];

            };

            $test = function ($container) use ($cb) {

                return $container->call($cb, []);

            };

            expect($test)->with($this->container)->to->throw(NoValueDefinedForParameterException::class);

        });

    });

});
