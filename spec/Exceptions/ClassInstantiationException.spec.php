<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ClassInstantiationException;
use Ellipse\Container\Exceptions\UnresolvedParameterException;

describe('ClassInstantiationException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $delegate = mock(UnresolvedParameterException::class)->get();

        $test = new ClassInstantiationException('class', $delegate);

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
