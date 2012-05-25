<?php

namespace Fjor\UnitTests\Support;

class ClassWithMethodDependency
{
	private $obj;

	public function set(\StdClass $obj)
	{
		$this->obj = $obj;
	}
}