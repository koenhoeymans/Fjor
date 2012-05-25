<?php

namespace Fjor\UnitTests\Support;

class ClassWithDependency
{
	private $constructorDep;

	public function __construct(\ArrayAccess $obj)
	{}
}