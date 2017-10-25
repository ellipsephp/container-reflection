<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\Resolver;
use Ellipse\Container\ReflectedParameter;

describe('Resolver', function () {

    beforeEach(function () {

        $this->resolver = new Resolver;

    });

    describe('->getValues()', function () {

        it('should sequentially call the ->getValue() method of the given array of reflected parameters', function () {

            $container = mock(ReflectionContainer::class)->get();
            $overrides = ['Class' => new class () {}];
            $defaults = ['v1', 'v2', 'v3'];

            $p1 = mock(ReflectedParameter::class);
            $p2 = mock(ReflectedParameter::class);
            $p3 = mock(ReflectedParameter::class);

            $p1->getValue->with($container, $overrides, ['v1', 'v2', 'v3'])
                ->returns(['v1', ['v2', 'v3']]);

            $p2->getValue->with($container, $overrides, ['v2', 'v3'])
                ->returns(['v2', ['v3']]);

            $p3->getValue->with($container, $overrides, ['v3'])
                ->returns(['v3', []]);

            $parameters = [$p1->get(), $p2->get(), $p3->get()];

            $test = $this->resolver->getValues($parameters, $container, $overrides, $defaults);

            expect($test)->toEqual(['v1', 'v2', 'v3']);

        });

    });

});
