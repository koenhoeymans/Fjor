<?php

namespace Fjor;

class ObjectGraphConstructorTest extends \PHPUnit\Framework\TestCase
{
    public function setup()
    {
        $this->factory = $this->getMockBuilder('\\Fjor\\ObjectFactory\\ObjectFactory')
            ->getMock();
        $this->dispatcher = $this->getMockBuilder('\\Epa\\Api\\EventDispatcher')
            ->getMock();
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
            ->will($this->returnValue(new \stdClass()));

        $obj = $this->ogc->getInstance('StdClass');

        $this->assertEquals($obj, new \stdClass());
    }

    /**
     * @test
     */
    public function exceptionWhenClassOrInterfaceDoesNotExist()
    {
        $this->expectException('Exception');
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
            ->will(
                $this->returnValue(
                    new \Fjor\ClassWithConstructorAndMethodDependency(
                        new \StdClass()
                    )
                )
            );

        $this->assertEquals(
            new \Fjor\ClassWithConstructorAndMethodDependency(
                new \StdClass()
            ),
            $this->ogc->getInstance('\\Fjor\\ClassWithConstructorAndMethodDependency')
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
                'Fjor\\ClassWithConstructorDependency',
                new \Fjor\Injection\InjectionMap(),
                $this->ogc
            )
            ->will(
                $this->returnValue(
                    new \Fjor\ClassWithOptionalDependency()
                )
            );

        $obj = $this->ogc->getInstance('Fjor\\ClassWithConstructorDependency');

        $this->assertEquals(
            $obj,
            new \Fjor\ClassWithOptionalDependency()
        );
    }

    /**
     * @test
     */
    public function throwsExceptionWhenNoBindingForInterface()
    {
        $this->expectException('Exception');
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
            new \SplObjectStorage(),
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
        $obj = new \Fjor\ClassWithMethodDependency();
        $obj->set(new \stdClass());

        $injectionMap = new \Fjor\Injection\InjectionMap();
        $injectionMap->add('set', array(new \stdClass()));

        $this->factory
            ->expects($this->once())
            ->method('createInstance')
            ->with(
                'Fjor\\ClassWithMethodDependency',
                $injectionMap,
                $this->ogc
            )
            ->will($this->returnValue($obj));

        $this->ogc->inject(
            'Fjor\\ClassWithMethodDependency',
            'set',
            array(new \stdClass())
        );

        $this->assertEquals(
            $obj,
            $this->ogc->getInstance('\\Fjor\\ClassWithMethodDependency')
        );
    }
}
