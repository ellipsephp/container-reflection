<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithOverriddenTypeHint;

describe('ExecutionWithOverriddenTypeHint', function () {

    beforeEach(function () {

        $this->overridden = mock(StdClass::class)->get();

        $this->factory = mock(ResolvedValueFactory::class);
        $this->overrides = ['overridden' => $this->overridden];
        $this->delegate = mock(ExecutionWithTypeHintInterface::class);

        $this->execution = new ExecutionWithOverriddenTypeHint(
            $this->factory->get(),
            $this->overrides,
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

        context('when the given class name is in the associative array of overrides', function () {

            it('should proxy the factory', function () {

                $resolved = new PartiallyResolvedValue($this->resolvable, $this->overridden);

                $this->factory->__invoke
                    ->with($resolved, $this->tail, ['p1', 'p2'])
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, 'overridden', $this->tail, $this->placeholders);

                expect($test)->toBe('value');

            });

        });

        context('when the given class name is not in the associative array of overrides', function () {

            it('should proxy the delegate', function () {

                $this->delegate->__invoke
                    ->with($this->resolvable, 'class', $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, 'class', $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
