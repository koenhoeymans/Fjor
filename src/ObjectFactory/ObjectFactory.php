<?php

namespace Fjor\ObjectFactory;

use Fjor\ObjectGraphConstructor;
use Fjor\Injection\InjectionMap;

interface ObjectFactory
{
    public function createInstance(
        $class,
        InjectionMap $injections,
        ObjectGraphConstructor $ogc
    );
}
