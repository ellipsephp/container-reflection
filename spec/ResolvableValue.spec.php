<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\ResolvableValue;
use Ellipse\Container\ResolvedValueFactory;

describe('ResolvableValue', function () {

    beforeEach(function () {

        $this->factory = stub();
        $this->parameters = [
            mock(ReflectionParameter::class)->get(),
            mock(ReflectionParameter::class)->get(),
        ];

        $this->resolvable = new ResolvableValue($this->factory, $this->parameters);

    });

    describe('->value()', function () {

        it('should use a new ResolvedValueFactory to execute the factory', function () {

            $container = mock(ReflectionContainer::class)->get();
            $overrides = ['TestClass' => new class () {}];
            $placeholders = ['p1', 'p2'];

            $factory = mock(ResolvedValueFactory::class);

            allow(ResolvedValueFactory::class)->toBe($factory->get());

            $factory->__invoke
                ->with($this->factory, $this->parameters, $placeholders)
                ->returns('value');

            $test = $this->resolvable->value($container, $overrides, $placeholders);

            expect($test)->toEqual('value');

        });

    });

});
