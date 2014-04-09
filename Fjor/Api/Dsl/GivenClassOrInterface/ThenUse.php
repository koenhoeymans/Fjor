<?php

/**
 * @package Fjor
 */
namespace Fjor\Api\Dsl\GivenClassOrInterface;

/**
 * @package Fjor
 */
interface ThenUse
{
	/**
	 * @param mixed $classOrInterfaceOrFactoryOrClosure
	 * @return void
	 */
	public function thenUse($classOrInterfaceOrFactoryOrClosure);
}