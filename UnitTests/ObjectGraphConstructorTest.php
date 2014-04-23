<?php

use Fjor\Dsl\Dsl;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_FjorTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->factory = $this->getMock('\\Fjor\\ObjectFactory\\ObjectFactory');
		$this->dispatcher = $this->getMock('\\Epa\\Api\\EventDispatcher');
		$this->ogc = new \Fjor\ObjectGraphConstructor($this->factory, $this->dispatcher);
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

		$obj = $this->ogc->getInstance('StdClass');

		$this->assertEquals($obj, new \stdClass());
	}

	/**
	 * @test
	 */
	public function exceptionWhenClassOrInterfaceDoesNotExist()
	{
		$this->setExpectedException('Exception');
		$this->ogc->getInstance('not exist');
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
	
		$this->ogc->setSingleton('SplObjectStorage');
	
		$this->assertSame(
			$this->ogc->getInstance('SplObjectStorage'),
			$this->ogc->getInstance('SplObjectStorage')
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
			$this->ogc->getInstance('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
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
				'Fjor\\EndToEndTests\\Support\\ClassWithDependency',
				new \Fjor\Injection\InjectionMap(),
				$this->ogc
			)
			->will($this->returnValue(
				new \Fjor\EndToEndTests\Support\ClassWithOptionalDependency())
			);
		
		$obj = $this->ogc->getInstance('Fjor\\EndToEndTests\\Support\\ClassWithDependency');
		
		$this->assertEquals(
			$obj,
			new \Fjor\EndToEndTests\Support\ClassWithOptionalDependency
		);
	}

	/**
	 * @test
	 */
	public function throwsExceptionWhenNoBindingForInterface()
	{
		$this->setExpectedException('Exception');
		$this->ogc->getInstance('\\SplSubject');
		$this->fail();
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

		$this->ogc->addBinding('ArrayAccess', 'SplObjectStorage');

		$this->assertEquals(
			new \SplObjectStorage,
			$this->ogc->getInstance('ArrayAccess')
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
		
		$this->ogc->addBinding('ArrayAccess', $obj);
		
		$this->assertSame(
			$obj,
			$this->ogc->getInstance('ArrayAccess')
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
	
		$this->ogc->addBinding('ArrayAccess', 'SplObjectStorage');
		$this->ogc->setSingleton('ArrayAccess');
	
		$this->assertSame(
			$this->ogc->getInstance('ArrayAccess'),
			$this->ogc->getInstance('ArrayAccess')
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
			->with(new \Fjor\Events\AfterNew('SplObjectStorage', new \SplObjectStorage()));

		$this->ogc->getInstance('SplObjectStorage');
		$this->ogc->getInstance('SplObjectStorage');
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
			->with(new \Fjor\Events\AfterNew('SplObjectStorage', new \SplObjectStorage()));
		$this->ogc->setSingleton('SplObjectStorage');

		$this->ogc->getInstance('SplObjectStorage');
		$this->ogc->getInstance('SplObjectStorage');
	}

	/**
	 * @test
	 */
	public function canSpecifyValuesForMethodToBeInjected()
	{
		$obj = new \Fjor\UnitTests\Support\ClassWithMethodDependency();
		$obj->set(new \stdClass());

		$injectionMap = new \Fjor\Injection\InjectionMap();
		$injectionMap->add('set', array(new \stdClass()));

		$this->factory
			->expects($this->once())
			->method('createInstance')
			->with('Fjor\\UnitTests\\Support\\ClassWithMethodDependency',
				   $injectionMap,
				   $this->ogc)
			->will($this->returnValue($obj));

		$this->ogc->inject(
			'Fjor\\UnitTests\\Support\\ClassWithMethodDependency',
			'set',
			array(new \stdClass())
		);

		$this->assertEquals(
			$obj,
			$this->ogc->getInstance('\\Fjor\\UnitTests\\Support\\ClassWithMethodDependency')
		);
	}
}