<?php

use function Eloquent\Phony\Kahlan\stub;
use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\CallableExecutionException;
use Ellipse\Container\Exceptions\UnresolvedParameterException;

describe('CallableExecutionException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $delegate = mock(UnresolvedParameterException::class)->get();

        $test = new CallableExecutionException(stub(), $delegate);

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
