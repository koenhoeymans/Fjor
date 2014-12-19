<?php

namespace Fjor;

class ClassWithMultipleConstructorArguments
{
    public function __construct($foo, \ArrayAccess $obj, $bar)
    {
    }
}
