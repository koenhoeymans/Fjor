<?php

namespace Fjor;

class ClassWithMethodDependency
{
    private $obj;

    public function set(\StdClass $obj)
    {
        $this->obj = $obj;
    }
}
