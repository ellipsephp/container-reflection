<?php

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Classes\Exceptions\ClassNotFoundException;

describe('ClassNotFoundException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test = new ClassNotFoundException('class');

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
