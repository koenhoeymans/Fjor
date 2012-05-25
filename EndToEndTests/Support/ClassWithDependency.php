<?php

namespace Fjor\EndToEndTests\Support;

class ClassWithDependency
{
	private $constructorDep;

	private $methodDep;

	public function __construct(\StdClass $obj)
	{
		$this->constructorDep = $obj;
	}

	public function getConstructorDependency()
	{
		return $this->constructorDep;
	}

	public function set(\StdClass $obj)
	{
		$this->methodDep = $obj;
	}

	public function getMethodDependency()
	{
		return $this->methodDep;
	}
}