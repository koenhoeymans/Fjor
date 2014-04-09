<?php

/**
 * @package Fjor
 */
namespace Fjor\Api\Dsl\GivenClassOrInterface;

/**
 * @package Fjor
 */
interface AndMethod
{
	/**
	 * @param string $method
	 * @return \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam
	 */
	public function andMethod($method);
}