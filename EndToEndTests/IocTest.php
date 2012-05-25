<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Fjor_EndToEndTests_IocTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->ioc = new \Fjor\Dsl\Dsl(
			new \Fjor\ObjectFactory\GenericObjectFactory()
		);
	}

	/**
	 * @test
	 */
	public function getsInstanceFromClassName()
	{
		$this->assertEquals(new \StdClass(), $this->ioc->get('\StdClass'));
	}

	/**
	 * @test
	 */
	public function aDifferentInstanceIsReturnedByGet()
	{
		$this->assertNotSame($this->ioc->get('StdClass'), $this->ioc->get('StdClass'));
	}

	/**
	 * @test
	 */
	public function aClassCanBeRegisteredAsSingleton()
	{
		$this->ioc->setSingleton('StdClass');

		$this->assertSame($this->ioc->get('StdClass'), $this->ioc->get('StdClass'));
	}

	/**
	 * @test
	 */
	public function aClassDependencyIsInjectedAutomatically()
	{
		$this->assertEquals(
			new \Fjor\EndToEndTests\Support\ClassWithDependency(new \StdClass()),
			$this->ioc->get('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
		);
	}

	/**
	 * @test
	 */
	public function anOptionalDependencyUsesDefaultValueIfNotSpecified()
	{
		$obj = $this->ioc->get(
			'\\Fjor\\EndToEndTests\\Support\\ClassWithOptionalDependency'
		);

		$this->assertNull($obj->getDependency());
	}

	/**
	 * @test
	 */
	public function anInterfaceDependencyIsLoadedDependingOnSpecifiedImplementation()
	{
		$this->ioc
				->given('ArrayAccess')
				->thenUse('SplObjectStorage');

		$this->assertEquals(new \SplObjectStorage(), $this->ioc->get('ArrayAccess'));
	}

	/**
	 * @test
	 */
	public function canUseInterfaceToInstance()
	{
		$obj = new \SplObjectStorage();

		$this->ioc
				->given('ArrayAccess')
				->thenUse($obj);

		$this->assertSame($obj, $this->ioc->get('ArrayAccess'));
	}

	/**
	 * @test
	 */
	public function canUseClassInSingletonScope()
	{
		$this->ioc->setSingleton('SplObjectStorage');

		$this->assertSame(
			$this->ioc->get('SplObjectStorage'), $this->ioc->get('SplObjectStorage')
		);
	}

	/**
	 * @test
	 */
	public function cangivenInterfaceAndClassInSingletonScope()
	{
		$this->ioc->given('ArrayAccess')->thenUse('SplObjectStorage')->inSingletonScope();

		$this->assertSame(
			$this->ioc->get('SplObjectStorage'), $this->ioc->get('SplObjectStorage')
		);
	}

	/**
	 * @test
	 */
	public function usingAnInstanceMeansSingletonScope()
	{
		$obj = new \SplObjectStorage();

		$this->ioc->given('ArrayAccess')->thenUse($obj);

		$this->assertSame(
			$this->ioc->get('ArrayAccess'), $this->ioc->get('ArrayAccess')
		);
	}

	/**
	 * @test
	 */
	public function arraysOrPrimitiveValuesCanBeGivenForConstructors()
	{
		$this->ioc
				->given('\\ArrayObject')
				->constructWith(array(array('5')));

		$obj = $this->ioc->get('\\ArrayObject');

		$this->assertEquals(new \ArrayObject(array(5)), $obj);
	}

	/**
	 * @test
	 */
	public function missingDependenciesAreAutoresolved()
	{
		$class = '\\Fjor\\EndToEndTests\\Support\\ClassWithMultipleConstructorArguments';

		$this->ioc->given('ArrayAccess')->thenUse('SplObjectStorage');

		$this->ioc
			->given($class)
			->constructWith(array(0 => 'foo', 2 => 'bar'));

		$this->assertEquals(
			 new $class('foo', new \SplObjectStorage(),	'bar'),
			 $this->ioc->get($class)
		);
	}

	/**
	 * @test
	 */
	public function bindingsCanBeSpecifiedAsInjectionParameter()
	{
		$class = '\\Fjor\\EndToEndTests\\Support\\ClassWithMultipleConstructorArguments';

		$this->ioc
			->given($class)
			->constructWith(array('foo', '\\SplObjectStorage', 'bar'));

		$this->assertEquals(
			new $class('foo', new \SplObjectStorage(),	'bar'),
			$this->ioc->get($class)
		);
	}

	/**
	 * @test
	 */
	public function specificConstructorBindingHasHigherWeightThanGeneral()
	{
		$class = '\\Fjor\\EndToEndTests\\Support\\ClassWithMultipleConstructorArguments';
		
		$this->ioc->given('ArrayAccess')->thenUse('\\ArrayObject');

		$this->ioc
			->given($class)
			->constructWith(array('foo', 'SplObjectStorage', 'bar'));
		
		$this->assertEquals(
			new $class('foo', new \SplObjectStorage(),	'bar'),
			$this->ioc->get($class)
		);
	}

	/**
	* @test
	*/
	public function specificConstructorBindingCanTakeObjectAsValue()
	{
		$class = '\\Fjor\\EndToEndTests\\Support\\ClassWithMultipleConstructorArguments';

		$this->ioc
			->given($class)
			->constructWith(array('foo', new \SplObjectStorage(), 'bar'));

		$this->assertEquals(
			new $class('foo', new \SplObjectStorage(),	'bar'),
			$this->ioc->get($class)
		);
	}

	/**
	 * @test
	 */
	public function objectCanBeSpecifiedGivenForMethodInjection()
	{
		$obj = new \stdClass();
		$this->ioc
				->given('SplObjectStorage')
				->andMethod('attach')
				->addParam(array($obj));
		$storage = $this->ioc->get('SplObjectStorage');

		$this->assertTrue($storage->contains($obj));
 	}

	/**
	 * @test
	 */
	public function primitivesCanBeSpecifiedForMethodInjection()
	{
		$this->ioc
				->given('SplStack')
				->andMethod('push')
				->addParam(array(5));

		$obj = new \SplStack();
		$obj->push(5);

		$this->assertEquals(
			$obj->pop(),
			$this->ioc->get('SplStack')->pop()
		);
 	}

	/**
	 * @test
	 */
	public function classesCanBeSpecifiedForMethodInjection()
	{
		$this->ioc
				->given('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
				->andMethod('set')
				->addParam(array('StdClass'));

		$obj = new \Fjor\EndToEndTests\Support\ClassWithDependency(new \StdClass());
		$obj->set(new StdClass);

		$this->assertEquals(
			$obj,
			$this->ioc->get('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
		);
	}

 	/**
 	 * @test
 	 */
 	public function methodCanBeSpecifiedForConstructionOfClass()
 	{
 		$this->ioc
 				->given('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency')
 				->andMethod('set')
 				->addParam();
 		$obj = $this->ioc->get('\\Fjor\\EndToEndTests\\Support\\ClassWithDependency');
 
 		$this->assertEquals(
 			new \stdClass(), $obj->getMethodDependency()
 		);
 	}

 	/**
 	 * @test
 	 */
 	public function sequencesOfArgumentsCanBeSpecifiedForInjection()
 	{
		$obj1 = new \stdClass();
		$obj2 = new \stdClass();
		$this->ioc
				->given('SplObjectStorage')
				->andMethod('attach')
				->addParam(array($obj1))
				->addParam(array($obj2));
		$storage = $this->ioc->get('SplObjectStorage');

		$this->assertTrue($storage->contains($obj1));
		$this->assertTrue($storage->contains($obj2));
 	}
}