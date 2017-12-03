<?php

use function Eloquent\Phony\Kahlan\stub;

use Ellipse\Container\PartiallyResolvedValue;

describe('PartiallyResolvedValue', function () {

    beforeEach(function () {

        $this->factory = stub();

        $this->partial = new PartiallyResolvedValue($this->factory, 'v1');

    });

    describe('->__invoke()', function () {

        it('should proxy the factory with the value as first parameter', function () {

            $this->factory->with('v1', 'v2', 'v3')->returns('result');

            $test = ($this->partial)('v2', 'v3');

            expect($test)->toEqual('result');

        });

        it('should work recursively', function () {

            $this->factory->with('v1', 'v2', 'v3')->returns('result');

            $partial = new PartiallyResolvedValue($this->partial, 'v2');

            $test = $partial('v3');

            expect($test)->toEqual('result');

        });


    });

});
