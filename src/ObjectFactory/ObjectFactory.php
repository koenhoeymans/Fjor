<?php

namespace Fjor\ObjectFactory;

Use Fjor\ObjectGraphConstructor;
Use Fjor\Injection\InjectionMap;

interface ObjectFactory
{
	public function createInstance(
		$class, InjectionMap $injections, ObjectGraphConstructor $ogc
	);
}