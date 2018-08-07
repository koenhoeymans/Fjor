<?php

namespace Fjor\ObjectFactory;

use Fjor\ObjectGraphConstructor;
use Fjor\Injection\InjectionMap;

interface ObjectFactory
{
    public function createInstance(
        string $class,
        InjectionMap $injections,
        ObjectGraphConstructor $ogc
    ) : object;
}
