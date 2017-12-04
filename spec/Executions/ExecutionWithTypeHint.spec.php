<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\ExecutionWithTypeHint;

describe('ExecutionWithTypeHint', function () {

    beforeEach(function () {

        $this->overridden = mock(StdClass::class)->get();

        $this->factory = mock(ResolvedValueFactory::class);
        $this->container = mock(ReflectionContainer::class);
        $this->overrides = ['overridden' => $this->overridden];
        $this->delegate = mock(ExecutionInterface::class);

        $this->execution = new ExecutionWithTypeHint(
            $this->factory->get(),
            $this->container->get(),
            $this->overrides,
            $this->delegate->get()
        );

    });

    it('should implement ExecutionInterface', function () {

        expect($this->execution)->toBeAnInstanceOf(ExecutionInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->resolvable = stub();

            $this->parameter = mock(ReflectionParameter::class);

            $this->tail = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

            $this->placeholders = ['p1', 'p2'];

        });

        context('when the parameter has a class type hint', function () {

            beforeEach(function () {

                $this->class = mock(ReflectionClass::class);

                $this->parameter->getClass->returns($this->class);

            });

            context('whe the parameter class name is a key of the associative array of overridden instances', function () {

                it('should proxy the factory with the associated instance', function () {

                    $this->class->getName->returns('overridden');

                    $resolved = new PartiallyResolvedValue($this->resolvable, $this->overridden);

                    $this->factory->__invoke
                        ->with($resolved, $this->tail, $this->placeholders)
                        ->returns('value');

                    $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                    expect($test)->toEqual('value');

                });

            });

            context('whe the parameter class name is not a key of the associative array of overridden instances', function () {

                it('should proxy the factory with the instance produced by the container ->make() method', function () {

                    $instance = mock(StdClass::class)->get();

                    $this->class->getName->returns('class');

                    $this->container->make->with('class', $this->overrides)->returns($instance);

                    $resolved = new PartiallyResolvedValue($this->resolvable, $instance);

                    $this->factory->__invoke
                        ->with($resolved, $this->tail, $this->placeholders)
                        ->returns('value');

                    $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                    expect($test)->toEqual('value');

                });

            });

        });

        context('when the parameters do not have a class type hint', function () {

            it('should proxy the delegate', function () {

                $this->parameter->getClass->returns(null);

                $this->delegate->__invoke
                    ->with($this->resolvable, $this->parameter, $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
