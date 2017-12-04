<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\Executions\ExecutionInterface;
use Ellipse\Container\Executions\FaillingExecution;
use Ellipse\Container\Executions\Exceptions\UnresolvedValueException;

describe('FaillingExecution', function () {

    beforeEach(function () {

        $this->execution = new FaillingExecution;

    });

    it('should implement ExecutionInterface', function () {

        expect($this->execution)->toBeAnInstanceOf(ExecutionInterface::class);

    });

    describe('->__invoke()', function () {

        it('should throw an UnresolvedValueException', function () {

            $resolvable = stub();

            $parameter = mock(ReflectionParameter::class)->get();

            $tail = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

            $placeholders = ['v1', 'v2'];

            $test = function () use ($resolvable, $parameter, $tail, $placeholders) {

                ($this->execution)($resolvable, $parameter, $tail, $placeholders);

            };

            $exception = new UnresolvedValueException($parameter);

            expect($test)->toThrow($exception);

        });

    });

});
