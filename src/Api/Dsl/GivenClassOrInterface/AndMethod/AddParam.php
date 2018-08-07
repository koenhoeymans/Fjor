<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface\AndMethod;

interface AddParam
{
    /**
     * Specifies the values that should be injected in a method. `addParam`
     * can be called more than once to inject other values.
     */
    public function addParam(array $values = array()) : AddParam;
}
