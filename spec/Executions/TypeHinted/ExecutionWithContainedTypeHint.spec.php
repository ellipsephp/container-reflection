<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithContainedTypeHint;

describe('ExecutionWithContainedTypeHint', function () {

    beforeEach(function () {

        $this->factory = mock(ResolvedValueFactory::class);
        $this->container = mock(ReflectionContainer::class);
        $this->delegate = mock(ExecutionWithTypeHintInterface::class);

        $this->execution = new ExecutionWithContainedTypeHint(
            $this->factory->get(),
            $this->container->get(),
            $this->delegate->get()
        );

    });

    it('should implement ExecutionWithTypeHintInterface', function () {

        expect($this->execution)->toBeAnInstanceOf(ExecutionWithTypeHintInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->resolvable = stub();

            $this->tail = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

            $this->placeholders = ['p1', 'p2'];

        });

        context('when the given class name is contained in the container', function () {

            it('should proxy the factory', function () {

                $instance = mock(StdClass::class)->get();

                $this->container->has->with('class')->returns(true);
                $this->container->get->with('class')->returns($instance);

                $resolved = new PartiallyResolvedValue($this->resolvable, $instance);

                $this->factory->__invoke
                    ->with($resolved, $this->tail, ['p1', 'p2'])
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, 'class', $this->tail, $this->placeholders);

                expect($test)->toBe('value');

            });

        });

        context('when the given class name is not contained in the container', function () {

            it('should proxy the delegate', function () {

                $this->container->has->with('class')->returns(false);

                $this->delegate->__invoke
                    ->with($this->resolvable, 'class', $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, 'class', $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
