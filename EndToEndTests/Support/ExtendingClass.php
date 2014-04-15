<?php

namespace Fjor\EndToEndTests\Support;

class ExtendingClass extends AbstractClass
{
	private $value;

	public function get()
	{
		return $this->value;
	}

	public function set($foo)
	{
		$this->value = $foo;
	}
}