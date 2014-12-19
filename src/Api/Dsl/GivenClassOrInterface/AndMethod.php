<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface;

interface AndMethod
{
    /**
     * @param  string                                                 $method
     * @return \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam
     */
    public function andMethod($method);
}
