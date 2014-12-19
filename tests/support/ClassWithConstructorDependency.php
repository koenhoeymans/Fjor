<?php

namespace Fjor;

class ClassWithConstructorDependency
{
    public function __construct(\ArrayAccess $obj)
    {
    }
}
