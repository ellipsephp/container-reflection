<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Executions\Exceptions\UnresolvedParameterException;

describe('UnresolvedParameterException', function () {

    beforeEach(function () {

        $reflection = mock(ReflectionParameter::class)->get();

        $this->exception = new UnresolvedParameterException($reflection);

    });

    it('should implement ContainerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

});
