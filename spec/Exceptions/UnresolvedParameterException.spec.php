<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\UnresolvedParameterException;

describe('UnresolvedParameterException', function () {

    beforeEach(function () {

        $this->parameter = mock(ReflectionParameter::class);

        $this->delegate = mock([Exception::class, ContainerExceptionInterface::class]);

        $this->exception = new UnresolvedParameterException($this->parameter->get(), $this->delegate->get());

    });

    it('should implement ContainerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

    describe('->parameter()', function () {

        it('should return the parameter', function () {

            $test = $this->exception->parameter();

            expect($test)->toBe($this->parameter->get());

        });

    });

});
