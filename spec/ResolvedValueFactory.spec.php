<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\ExecutionWithTypeHint;

describe('ResolvedValueFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(ExecutionInterface::class);

        allow(ExecutionWithTypeHint::class)->toBe($this->delegate->get());

        $this->container = mock(ReflectionContainer::class);
        $this->overrides = [StdClass::class => mock(StdClass::class)->get()];

        $this->factory = new ResolvedValueFactory($this->container->get(), $this->overrides);

    });

    describe('->__invoke()', function () {

        context('when the parameter list is not empty', function () {

            it('should proxy the delegate', function () {

                $factory = stub();
                $parameter1 = mock(ReflectionParameter::class)->get();
                $parameter2 = mock(ReflectionParameter::class)->get();
                $placeholders = ['p1', 'p2'];

                $this->delegate->__invoke
                    ->with($factory, $parameter1, [$parameter2], $placeholders)
                    ->returns('value');

                $test = ($this->factory)($factory, [$parameter1, $parameter2], $placeholders);

                expect($test)->toEqual('value');

            });

        });

        context('when the parameter list is empty', function () {

            it('should proxy the factory', function () {

                $factory = stub();
                $placeholders = ['p1', 'p2'];

                $factory->returns('value');

                $test = ($this->factory)($factory, [], $placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
