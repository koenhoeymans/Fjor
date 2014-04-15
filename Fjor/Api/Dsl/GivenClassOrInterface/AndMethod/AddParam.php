<?php

namespace Fjor\Api\Dsl\GivenClassOrInterface\AndMethod;

interface AddParam
{
	/**
	 * @param array $values
	 * 
	 * @return AddParam
	 */
	public function addParam(array $values = array());
}