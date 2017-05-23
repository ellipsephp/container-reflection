<?php

use Psr\Container\ContainerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Exceptions\NoValueDefinedForParameterException;

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

class DummyClass
{
    private $parameters = [];

    public function __construct(DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5')
    {
        $this->parameters[] = $arg1;
        $this->parameters[] = $arg2;
        $this->parameters[] = $arg3;
        $this->parameters[] = $arg4;
        $this->parameters[] = $arg5;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}

class DummyClassWithoutConstructor
{
    public function getParameters()
    {
        return [];
    }
}

class DummyClassStatic
{
    static public function getInstance(DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5')
    {
        return new DummyClass($arg1, $arg2, $arg3, $arg4, $arg5);
    }
}

describe('ReflectionContainer', function () {

    beforeEach(function () {

        $this->wrapped = Mockery::mock(ContainerInterface::class);
        $this->container = new ReflectionContainer($this->wrapped);

    });

    it('should implements container interface', function () {

        expect($this->container)->to->be->an->instanceof(ContainerInterface::class);

    });

    describe('->get()', function () {

        it('should proxy the underlying container get method', function () {

            $alias = 'test';
            $instance = new class {};

            $this->wrapped->shouldReceive('get')
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

            $this->wrapped->shouldReceive('has')
                ->with($alias)
                ->andReturn($expected);

            $test = $this->container->has($alias);

            expect($test)->to->be->equal($expected);

        });

        it('should call the underlying container has method and return false when it failed', function () {

            $alias = 'test';
            $expected = false;

            $this->wrapped->shouldReceive('has')
                ->with($alias)
                ->andReturn($expected);

            $test = $this->container->has($alias);

            expect($test)->to->be->equal($expected);

        });

    });

    describe('->make()', function () {

        it('should work as expected', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $test = $this->container->make(DummyClass::class, [
                DummyArg2::class => $arg2,
            ], [
                'arg4' => $arg4,
                $arg3,
            ]);

            $parameters = $test->getParameters();

            expect($parameters)->to->be->equal([$arg1, $arg2, $arg3, $arg4, $arg5]);

        });

        it('should work when the class has no constructor', function () {

            $test = $this->container->make(DummyClassWithoutConstructor::class);

            $parameters = $test->getParameters();

            expect($parameters)->to->be->equal([]);

        });

        it('should use make when a parameter type hinted as a class is neither in overides nor the container', function () {

            $arg1 = 'arg1';
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(false);

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg2::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg2::class)
                ->andReturn($arg2);

            $test = $this->container->make(DummyClass::class, [
                DummyArg2::class => $arg2,
            ], [
                $arg3,
                $arg4,
                $arg5,
                'value' => $arg1,
            ]);

            $parameters = $test->getParameters();

            $parameter1 = array_shift($parameters);

            expect($parameter1)->to->be->an->instanceof(DummyArg1::class);
            expect($parameter1->getValue())->to->be->equal($arg1);
            expect($parameters)->to->be->equal([$arg2, $arg3, $arg4, $arg5]);

        });

        it('should fail when it cant resolve one parameter', function () {

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn(new DummyArg1('arg1'));

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg2::class)
                ->andReturn(true);

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
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $cb = function (DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5') {

                return [$arg1, $arg2, $arg3, $arg4, $arg5];

            };

            $test = $this->container->call($cb, [
                DummyArg2::class => $arg2,
            ], [
                'arg4' => $arg4,
                $arg3,
            ]);

            expect($test)->to->be->equal([$arg1, $arg2, $arg3, $arg4, $arg5]);

        });

        it('should work with class method', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $class = new class {

                public function test (DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5') {

                    return [$arg1, $arg2, $arg3, $arg4, $arg5];

                }

            };

            $test = $this->container->call([$class, 'test'], [
                DummyArg2::class => $arg2,
            ], [
                'arg4' => $arg4,
                $arg3,
            ]);

            expect($test)->to->be->equal([$arg1, $arg2, $arg3, $arg4, $arg5]);

        });

        it('should work with static class method', function () {

            $arg1 = new DummyArg1('arg1');
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn($arg1);

            $test = $this->container->call('DummyClassStatic::getInstance', [
                DummyArg2::class => $arg2,
            ], [
                'arg4' => $arg4,
                $arg3,
            ]);

            $parameters = $test->getParameters();

            expect($parameters)->to->be->equal([$arg1, $arg2, $arg3, $arg4, $arg5]);

        });

        it('should use make when a parameter type hinted as a class is neither in overides nor the container', function () {

            $arg1 = 'arg1';
            $arg2 = new DummyArg2('arg2');
            $arg3 = 'arg3';
            $arg4 = 'arg4';
            $arg5 = 'arg5';

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(false);

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg2::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg2::class)
                ->andReturn($arg2);

            $cb = function (DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5') {

                return [$arg1, $arg2, $arg3, $arg4, $arg5];

            };

            $test = $this->container->call($cb, [
                DummyArg2::class => $arg2,
            ], [
                $arg3,
                $arg4,
                $arg5,
                'value' => $arg1,
            ]);

            $parameter1 = array_shift($test);

            expect($parameter1)->to->be->an->instanceof(DummyArg1::class);
            expect($parameter1->getValue())->to->be->equal($arg1);
            expect($test)->to->be->equal([$arg2, $arg3, $arg4, $arg5]);

        });

        it('should fail when it cant resolve one parameter', function () {

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg1::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg1::class)
                ->andReturn(new DummyArg1('arg1'));

            $this->wrapped->shouldReceive('has')
                ->with(DummyArg2::class)
                ->andReturn(true);

            $this->wrapped->shouldReceive('get')
                ->with(DummyArg2::class)
                ->andReturn(new DummyArg2('arg2'));

            $cb = function (DummyArg1 $arg1, DummyArg2 $arg2, $arg3, $arg4, $arg5 = 'arg5') {

                return [$arg1, $arg2, $arg3, $arg4, $arg5];

            };

            $test = function ($container) use ($cb) {

                return $container->call($cb, []);

            };

            expect($test)->with($this->container)->to->throw(NoValueDefinedForParameterException::class);

        });

    });

});
