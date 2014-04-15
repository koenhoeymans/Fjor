<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_FjorFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function createsDefaultInstance()
	{
		$eventDispatcher = \Epa\EventDispatcherFactory::create();
		$fjorDsl = new \Fjor\FjorDsl(
			new \Fjor\Fjor(
				new \Fjor\ObjectFactory\GenericObjectFactory(),
				$eventDispatcher
			),
			$eventDispatcher
		);

		$this->assertEquals(\Fjor\FjorFactory::create(), $fjorDsl);
	}
}