<?php

namespace Fjor\UnitTests\Support;

class ClassWithOptionalDependency
{
	private $arr;

	public function __construct(array $arr = null)
	{
		$this->arr = $arr;
	}

	public function getDependency()
	{
		return $this->arr;
	}
}