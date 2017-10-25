<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException;

describe('ParameterValueCantBeResolvedException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $reflection = mock(ReflectionParameter::class)->get();

        $test =new ParameterValueCantBeResolvedException($reflection);

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
