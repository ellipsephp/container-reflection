<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Executions\Exceptions\UnresolvedValueException;

describe('UnresolvedValueException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test = new UnresolvedValueException;

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
