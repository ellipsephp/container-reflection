<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\ExecutionWithPlaceholder;

describe('ExecutionWithPlaceholder', function () {

    beforeEach(function () {

        $this->factory = mock(ResolvedValueFactory::class);
        $this->delegate = mock(ExecutionInterface::class);

        $this->execution = new ExecutionWithPlaceholder($this->factory->get(), $this->delegate->get());

    });

    it('should implement ExecutionInterface', function () {

        expect($this->execution)->toBeAnInstanceOf(ExecutionInterface::class);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->resolvable = stub();

            $this->parameter = mock(ReflectionParameter::class)->get();

            $this->tail = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

        });

        context('when the given array of placeholders is not empty', function () {

            it('should proxy the factory without the first placeholder', function () {

                $resolved = new PartiallyResolvedValue($this->resolvable, 'p1');

                $this->factory->__invoke
                    ->with($resolved, $this->tail, ['p2'])
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter, $this->tail, ['p1', 'p2']);

                expect($test)->toEqual('value');

            });

        });

        context('when the given array of placeholders is empty', function () {

            it('should proxy the delegate', function () {

                $this->delegate->__invoke
                    ->with($this->resolvable, $this->parameter, $this->tail, [])
                    ->returns('value');

                $test = ($this->execution)($this->resolvable, $this->parameter, $this->tail, []);

                expect($test)->toEqual('value');

            });

        });

    });

});
