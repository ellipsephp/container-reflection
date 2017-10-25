<?php

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ImplementationNotDefinedException;

describe('ImplementationNotDefinedException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test =new ImplementationNotDefinedException('id');

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
