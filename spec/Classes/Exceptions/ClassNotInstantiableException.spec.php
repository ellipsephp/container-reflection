<?php

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Classes\Exceptions\ClassNotInstantiableException;

describe('ClassNotInstantiableException', function () {

    it('should implement ContainerExceptionInterface', function () {

        $test = new ClassNotInstantiableException('class');

        expect($test)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
