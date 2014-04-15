<?php

use Fjor\Dsl\Dsl;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_FjorTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->factory = $this->getMock('\\Fjor\\ObjectFactory\\ObjectFactory');
		$this->dispatcher = $this->getMock('\\Epa\\Api\\EventDispatcher');
		$this->fjor = new \Fjor\Fjor($this->factory, $this->dispatcher);
	}

	/**
	 * @test
	 */
	public function getsClassInstance()
	{
		$this->factory
			->expects($this->atLeastOnce())
			->method('createInstance')
			->will($this->returnValue(new stdClass()));

		$obj = $this->fjor->get('StdClass');

		$this->assertEquals($obj, new \stdClass());
	}

	/**
	 * @test
	 */
	public function exceptionWhenClassOrInterfaceDoesNotExist()
	{
		try {
			$this->fjor->get('not exist');
			$this->fail();
		}
		catch (Exception $e)
		{}
	}

	/**
	 * @test
	 */
	public function classCanBeSingleton()
	{
		$this->factory
			->expects($this->once())
			->method('createInstance')
			->will($this->returnValue(new \SplObjectStorage()));
	
		$this->fjor->setSingleton('SplObjectStorage');
	
		$this->assertSame(
			$this->fjor->get('SplObjectStorage'),
			$this->fjor->get('SplObjectStorage')
		);
	}

	/**
	 * @test
	 */
	public function dependenciesAreAutoInjected()
	{
		$this->factory
			->expects($this->atLeastOnce())
			->method('createInstance')
			->will($this->returnValue(
				new \Fjor\EndToEndTests\Support\ClassWithDependency(
					new \StdClass()
				))
			);

		$this->assertEquals(
			new \Fjor\EndToEndTests\Support\ClassWithDependency(
				new \StdClass()
			),
			$this->fjor->get('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
		);
	}

	/**
	 * @test
	 */
	public function optionalArgumentsAreNotGivenForInjectionIfNotSpecified()
	{
		$this->factory
			->expects($this->atLeastOnce())
			->method('createInstance')
			->with(
				'\\Fjor\\EndToEndTests\\Support\\ClassWithDependency',
				new \Fjor\Injection\InjectionMap(),
				$this->fjor
			)
			->will($this->returnValue(
				new \Fjor\EndToEndTests\Support\ClassWithOptionalDependency())
			);
		
		$obj = $this->fjor->get('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency');
		
		$this->assertEquals(
			$obj,
			new \Fjor\EndToEndTests\Support\ClassWithOptionalDependency
		);
	}

	/**
	 * @test
	 */
	public function implementingClassesCanBeSpecified()
	{
		$this->factory
			->expects($this->atLeastOnce())
			->method('createInstance')
			->will($this->returnValue(new \SplObjectStorage()));

		$this->fjor->addBinding('ArrayAccess', 'SplObjectStorage');

		$this->assertEquals(
			new \SplObjectStorage,
			$this->fjor->get('ArrayAccess')
		);
	}

	/**
	 * @test
	 */
	public function objectsCanBeBound()
	{
		$obj = new \SplObjectStorage();

		$this->factory
			->expects($this->never())
			->method('createInstance');
		
		$this->fjor->addBinding('ArrayAccess', $obj);
		
		$this->assertSame(
			$obj,
			$this->fjor->get('ArrayAccess')
		);
	}

	/**
	 * @test
	 */
	public function interfaceCanBeSingleton()
	{
		$this->factory
			->expects($this->once())
			->method('createInstance')
			->will($this->returnValue(new \SplObjectStorage()));
	
		$this->fjor->addBinding('ArrayAccess', 'SplObjectStorage');
		$this->fjor->setSingleton('ArrayAccess');
	
		$this->assertSame(
			$this->fjor->get('ArrayAccess'),
			$this->fjor->get('ArrayAccess')
		);
	}

	/**
	 * @test
	 */
	public function throwsEventAfterNewlyCreatedObject()
	{
		$this->factory
			->expects($this->exactly(2))
			->method('createInstance')
			->will($this->returnValue(new \SplObjectStorage()));
		$this->dispatcher->expects($this->exactly(2))
			->method('notify')
			->with(new \Fjor\Events\AfterNew('\\SplObjectStorage', new \SplObjectStorage()));

		$this->fjor->get('SplObjectStorage');
		$this->fjor->get('SplObjectStorage');
	}

	/**
	 * @test
	 */
	public function singletonThrowsOnlyOneNewlyCreatedEvent()
	{
		$this->factory
			->expects($this->once())
			->method('createInstance')
			->will($this->returnValue(new \SplObjectStorage()));
		$this->dispatcher->expects($this->once())
			->method('notify')
			->with(new \Fjor\Events\AfterNew('\\SplObjectStorage', new \SplObjectStorage()));
		$this->fjor->setSingleton('SplObjectStorage');

		$this->fjor->get('SplObjectStorage');
		$this->fjor->get('SplObjectStorage');
	}
}