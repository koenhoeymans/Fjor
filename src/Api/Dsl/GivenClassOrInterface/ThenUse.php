<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface;

interface ThenUse
{
    /**
     * @param mixed $classOrInterfaceOrFactoryOrClosure
     *
     * @return void
     */
    public function thenUse($classOrInterfaceOrFactoryOrClosure);
}
