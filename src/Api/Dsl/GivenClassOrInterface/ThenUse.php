<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface;

interface ThenUse
{
    /**
     * @param mixed $classOrInterfaceOrFactoryOrClosure
     */
    public function thenUse($classOrInterfaceOrFactoryOrClosure) : void;
}
