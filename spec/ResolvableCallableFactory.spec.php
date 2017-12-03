<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvableValue;
use Ellipse\Container\ResolvableCallableFactory;
use Ellipse\Container\Callables\ClosureReflectionFactory;

describe('ResolvableCallableFactory', function () {

    beforeEach(function () {

        $this->delegate = mock(ClosureReflectionFactory::class);

        allow(ClosureReflectionFactory::class)->toBe($this->delegate->get());

        $this->factory = new ResolvableCallableFactory;

    });

    describe('->__invoke()', function () {

        it('should return a new ResolvableValue from the given callable', function () {

            $callable = stub();
            $reflection = mock(ReflectionFunctionAbstract::class);
            $parameters = [
                mock(ReflectionParameter::class)->get(),
                mock(ReflectionParameter::class)->get(),
            ];

            $this->delegate->__invoke->with($callable)->returns($reflection);

            $reflection->getParameters->returns($parameters);

            $test = ($this->factory)($callable);

            $resolvable = new ResolvableValue($callable, $parameters);

            expect($test)->toEqual($resolvable);

        });

    });

});
