<?php

namespace Fjor;

class FjorFactory
{
	/**
	 * Creates a default setup for Fjor.
	 * 
	 * @return \Fjor\Api\ObjectGraphConstructor
	 */
	public static function create()
	{
		$eventDispatcher = \Epa\EventDispatcherFactory::create();
		$fjorDsl = new \Fjor\FjorDsl(
			new \Fjor\ObjectGraphConstructor(
				new \Fjor\ObjectFactory\GenericObjectFactory(),
				$eventDispatcher
			),
			$eventDispatcher
		);

		return $fjorDsl;
	}
}