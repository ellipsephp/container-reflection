<?php

use function Eloquent\Phony\Kahlan\stub;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Callables\Exceptions\CallableFormatNotSupportedException;

describe('CallableFormatNotSupportedException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test = new CallableFormatNotSupportedException(stub());

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
