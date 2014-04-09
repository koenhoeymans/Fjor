<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_FjorFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function createsDefaultInstance()
	{
		$factoryFjor = \Fjor\FjorFactory::createDefaultSetup();

		$defaultFactory = new \Fjor\ObjectFactory\GenericObjectFactory();
		$eventDispatcher = new \Epa\EventDispatcher();
		$fjor = new \Fjor\FjorDsl($defaultFactory, $eventDispatcher);
		$fjor->addObserver($eventDispatcher);

		$this->assertEquals($factoryFjor, $fjor);
	}
}