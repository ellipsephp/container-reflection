<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Resolver;
use Ellipse\Container\ParameterResolver;

describe('Resolver', function () {

    beforeEach(function () {

        $this->parameter = mock(ParameterResolver::class);

        $this->resolver = new Resolver($this->parameter->get());

    });

    describe('::getInstance()', function () {

        it('should return a Resolver', function () {

            $test = Resolver::getInstance();

            expect($test)->toBeAnInstanceOf(Resolver::class);

        });

    });

    describe('->map()', function () {

        it('should call the ->resolve() method of the parameter resolver for each parameter in the given array', function () {

            $container = mock(ReflectionContainer::class)->get();

            $p1 = mock(ReflectionParameter::class)->get();
            $p2 = mock(ReflectionParameter::class)->get();
            $p3 = mock(ReflectionParameter::class)->get();

            $overrides = ['Class' => new class () {}];

            $this->parameter->resolve->with($container, $p1, $overrides, ['v1', 'v2', 'v3'])
                ->returns(['v1', ['v2', 'v3']]);

            $this->parameter->resolve->with($container, $p2, $overrides, ['v2', 'v3'])
                ->returns(['v2', ['v3']]);

            $this->parameter->resolve->with($container, $p3, $overrides, ['v3'])
                ->returns(['v3', []]);

            $test = $this->resolver->map(
                $container,
                [$p1, $p2, $p3],
                $overrides,
                ['v1', 'v2', 'v3']
            );

            expect($test)->toEqual(['v1', 'v2', 'v3']);
            $this->parameter->resolve->called();

        });

    });

});
