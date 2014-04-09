<?php

namespace Fjor;

class FjorFactory
{
	/**
	 * Creates a default setup for Fjor.
	 * 
	 * @return \Fjor\Api\ObjectGraphConstructor
	 */
	public static function createDefaultSetup()
	{
		$defaultFactory = new \Fjor\ObjectFactory\GenericObjectFactory();
		$eventDispatcher = new \Epa\EventDispatcher();
		$fjor = new \Fjor\FjorDsl($defaultFactory, $eventDispatcher);
		$fjor->addObserver($eventDispatcher);

		return $fjor;
	}
}