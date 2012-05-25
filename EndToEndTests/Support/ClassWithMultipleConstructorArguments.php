<?php

namespace Fjor\EndToEndTests\Support;

class ClassWithMultipleConstructorArguments
{
	public function __construct($foo, \ArrayAccess $obj, $bar)
	{}
}