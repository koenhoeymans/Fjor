<?php

require_once dirname(__FILE__)
	. DIRECTORY_SEPARATOR . '..' 
	. DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_ObjectFactory_GenericObjectFactoryTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->ogc = $this->getMockBuilder('\\Fjor\\ObjectGraphConstructor')
								->disableOriginalConstructor()
								->getMock();
		$this->factory = new \Fjor\ObjectFactory\GenericObjectFactory(); 
	}

	private function createInjectionMap()
	{
		return new \Fjor\Injection\InjectionMap();
	}

	/**
	 * @test
	 */
	public function createsInstanceOfClass()
	{
		$this->assertEquals(
			new \stdClass(),
			$this->factory->createInstance(
				'StdClass', $this->createInjectionMap(), $this->ogc
			)
		);
	}

	/**
	 * @test
	 */
	public function createsInstanceWithSpecifiedConstructorArguments()
	{
		$this->assertEquals(
			new \ArrayObject(array('foo')),
			$this->factory->createInstance(
				'ArrayObject',
				$this->createInjectionMap()->add('__construct', array(array('foo'))),
				$this->ogc
			)
		);
	}

	/**
	 * @test
	 */
	public function optionalArgumentsAreNotInjectedIfNotSpecified()
	{
		$this->assertEquals(
			new \Fjor\UnitTests\Support\ClassWithOptionalDependency(),
			$this->factory->createInstance(
				'\\Fjor\\UnitTests\\Support\\ClassWithOptionalDependency',
				$this->createInjectionMap(),
				$this->ogc
			)
		);
	}

	/**
	 * @test
	 */
	public function triesToFindBindingInSpecifiedArguments()
	{
		$class = '\\Fjor\\UnitTests\\Support\\ClassWithDependency';

		$this->ogc
			->expects($this->once())
			->method('getInstance')
			->with('\\SplObjectStorage')
			->will($this->returnValue(new \SplObjectStorage));

		$this->assertEquals(
			new $class(new \SplObjectStorage()),
			$this->factory->createInstance(
				$class,
				$this->createInjectionMap()->add('__construct', array('\\SplObjectStorage')),
				$this->ogc
			)
		);
	}

	/**
	 * @test
	 */
	public function canTakeObjectAsParameter()
	{
		$class = '\\Fjor\\UnitTests\\Support\\ClassWithDependency';

		$this->ogc
			->expects($this->never())
			->method('getInstance');

		$this->assertEquals(
			new $class(new \SplObjectStorage()),
			$this->factory->createInstance(
				$class,
				$this->createInjectionMap()->add('__construct', array(new \SplObjectStorage())), 
				$this->ogc
			)
		);
	}

	/**
	 * @test
	 */
	public function injectsSpecifiedParametersInMethods()
	{
		$class = '\\Fjor\\UnitTests\\Support\\ClassWithMethodDependency';
		
		$this->ogc
			->expects($this->once())
			->method('getInstance')
			->with('StdClass')
			->will($this->returnValue(new \StdClass()));

		$obj = new $class();
		$obj->set(new \stdClass());
		
		$this->assertEquals(
			$obj,
			$this->factory->createInstance(
				$class,
				$this->createInjectionMap()->add('set', array('StdClass')),
				$this->ogc
			)
		);
	}
}