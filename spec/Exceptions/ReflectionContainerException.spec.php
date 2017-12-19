<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ReflectionContainerException;
use Ellipse\Resolvable\Exceptions\ResolvingExceptionInterface;

describe('ReflectionContainerException', function () {

    beforeEach(function () {

        $this->previous = mock([Throwable::class, ResolvingExceptionInterface::class])->get();

        $this->exception = new ReflectionContainerException('class', $this->previous);

    });

    it('should implement ContainerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

    describe('->getPrevious()', function () {

        it('should return the previous exception', function () {

            $test = $this->exception->getPrevious();

            expect($test)->toBe($this->previous);

        });

    });

});
