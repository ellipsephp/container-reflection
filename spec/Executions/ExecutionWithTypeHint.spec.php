<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\ExecutionWithTypeHint;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface;

describe('ExecutionWithTypeHint', function () {

    beforeEach(function () {

        $this->typehinted = mock(ExecutionWithTypeHintInterface::class);
        $this->delegate = mock(ExecutionInterface::class);

        $this->execution = new ExecutionWithTypeHint(
            $this->typehinted->get(),
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

        context('when the given parameter has a class type hint', function () {

            it('should proxy the execution', function () {

                $class = mock(ReflectionClass::class);

                $class->getName->returns('class');

                $this->parameter->getClass->returns($class);

                $instance = mock(StdClass::class)->get();

                $this->typehinted->__invoke
                    ->with($this->resolvable, 'class', $this->tail, $this->placeholders)
                    ->returns($instance);

                $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                expect($test)->toBe($instance);

            });

        });

        context('when the given parameter dont have a class type hint', function () {

            it('should proxy the delegate', function () {

                $this->parameter->getClass->returns(null);

                $this->delegate->__invoke
                    ->with($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
