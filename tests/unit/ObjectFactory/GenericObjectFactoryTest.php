<?php

namespace Fjor\ObjectFactory;

class GenericObjectFactoryTest extends \PHPUnit_Framework_TestCase
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
            new \Fjor\ClassWithOptionalDependency(),
            $this->factory->createInstance(
                '\\Fjor\\ClassWithOptionalDependency',
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
        $class = '\\Fjor\\ClassWithConstructorDependency';

        $this->ogc
            ->expects($this->once())
            ->method('getInstance')
            ->with('\\SplObjectStorage')
            ->will($this->returnValue(new \SplObjectStorage()));

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
        $class = '\\Fjor\\ClassWithConstructorDependency';

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
        $class = '\\Fjor\\ClassWithMethodDependency';

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

    /**
     * @test
     */
    public function detectsWhenParamterIsOptional()
    {
        // SplObjectStorage::attach has an optional second parameter
        $this->factory->createInstance(
            'SplObjectStorage',
            $this->createInjectionMap()->add('attach', array(new \StdClass())),
            $this->ogc
        );
    }

    /**
     * @test
     */
    public function throwsExceptionWhenCantFindValueForParameter()
    {
        $this->setExpectedException('Exception');
        $this->factory->createInstance(
            'SplObjectStorage',
            $this->createInjectionMap()->add('attach', array()),
            $this->ogc
        );
    }
}
