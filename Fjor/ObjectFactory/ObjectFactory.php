<?php

namespace Fjor\ObjectFactory;

Use Fjor\Fjor;
Use Fjor\Injection\InjectionMap;

interface ObjectFactory
{
	public function createInstance($class, InjectionMap $injections, Fjor $ioc);
}