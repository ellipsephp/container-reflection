<?php

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ClassNotFoundException;

describe('ClassNotFoundException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test =new ClassNotFoundException('id');

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
