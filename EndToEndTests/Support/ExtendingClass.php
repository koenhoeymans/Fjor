<?php

/**
 * @package Fjor
 */
namespace Fjor\EndToEndTests\Support;

/**
 * @package Fjor
 */
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