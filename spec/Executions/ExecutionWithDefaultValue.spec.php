<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\ExecutionWithDefaultValue;

describe('ExecutionWithDefaultValue', function () {

    beforeEach(function () {

        $this->factory = mock(ResolvedValueFactory::class);
        $this->delegate = mock(ExecutionInterface::class);

        $this->execution = new ExecutionWithDefaultValue($this->factory->get(), $this->delegate->get());

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

            $this->placeholders = ['v1', 'v2'];

        });

        context('when the given parameter has a default value', function () {

            it('should proxy the factory', function () {

                $this->parameter->isDefaultValueAvailable->returns(true);
                $this->parameter->getDefaultValue->returns('default');

                $resolved = new PartiallyResolvedValue($this->resolvable, 'default');

                $this->factory->__invoke
                    ->with($resolved, $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

        context('when the given array of placeholders is empty', function () {

            it('should proxy the delegate', function () {

                $this->parameter->isDefaultValueAvailable->returns(false);

                $this->delegate->__invoke
                    ->with($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders)
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter->get(), $this->tail, $this->placeholders);

                expect($test)->toEqual('value');

            });

        });

    });

});
