<?php

namespace Fjor;

class ClassWithMethodDependency
{
    private $obj;

    public function set(\StdClass $obj)
    {
        $this->obj = $obj;
    }

    public function get()
    {
        return $this->obj;
    }
}
