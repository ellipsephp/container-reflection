<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Container\ResolvedValueFactory;
use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\PartiallyResolvedValue;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithTypeHintInterface;
use Ellipse\Container\Executions\TypeHinted\ExecutionWithClassTypeHint;

describe('ExecutionWithClassTypeHint', function () {

    beforeEach(function () {

        $this->factory = mock(ResolvedValueFactory::class);
        $this->container = mock(ReflectionContainer::class);
        $this->overrides = ['class1' => 'instance1', 'class2' => 'instance2'];

        $this->execution = new ExecutionWithClassTypeHint(
            $this->factory->get(),
            $this->container->get(),
            $this->overrides
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

        it('should proxy the factory', function () {

            $instance = mock(StdClass::class)->get();

            $this->container->make->with('class', $this->overrides)->returns($instance);

            $resolved = new PartiallyResolvedValue($this->resolvable, $instance);

            $this->factory->__invoke
                ->with($resolved, $this->tail, ['p1', 'p2'])
                ->returns('value');

            $test = ($this->execution)($this->resolvable, 'class', $this->tail, $this->placeholders);

            expect($test)->toBe('value');

        });

    });

});
