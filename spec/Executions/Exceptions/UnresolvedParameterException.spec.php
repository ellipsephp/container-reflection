<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Executions\Exceptions\UnresolvedParameterException;

describe('UnresolvedParameterException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $reflection = mock(ReflectionParameter::class)->get();

        $test = new UnresolvedParameterException($reflection);

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
