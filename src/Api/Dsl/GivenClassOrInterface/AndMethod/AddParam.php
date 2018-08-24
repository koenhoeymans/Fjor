<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface\AndMethod;

interface AddParam
{
    /**
     * Specifies the values that should be injected in a method.
     * 
     * Example: `addParam(2, 4, 8)`
     * 
     * `addParam`can be called more than once to inject other values. The
     * method then gets called multiple times.
     */
    public function addParam(...$values) : AddParam;
}
