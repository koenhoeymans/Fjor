<?php

namespace Fjor;

class FjorDslTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->ogc = $this->getMockBuilder('\\Fjor\\ObjectGraphConstructor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder('\\Epa\\Api\\EventDispatcher')
            ->getMock();
        $this->dsl = new \Fjor\FjorDsl($this->ogc, $this->eventDispatcher);
    }

    /**
     * @test
     */
    public function addsPluginsToEventDispatcher()
    {
        $plugin = $this->getMockBuilder('\\Epa\\Api\\Plugin')->getMock();
        $this->eventDispatcher
            ->expects($this->once())
            ->method('addPlugin')
            ->with($plugin);

        $this->dsl->addPlugin($plugin);
    }

    /**
     * @test
     */
    public function getsObjectFromFjor()
    {
        $this->ogc
            ->expects($this->once())
            ->method('getInstance')
            ->with('SplSubject');

        $this->dsl->get('SplSubject');
    }

    /**
     * @test
     */
    public function providesGivenThenUseForBindingInterfacesToClasses()
    {
        $this->ogc
            ->expects($this->once())
            ->method('addBinding')
            ->with('Foo', 'Bar');

        $this->dsl->given('Foo')->thenUse('Bar');
    }

    /**
     * @test
     */
    public function providesDslForSpecifyingConstructorValues()
    {
        $this->ogc
            ->expects($this->once())
            ->method('inject')
            ->with('Foo', '__construct', array('value'));

        $this->dsl->given('Foo')->constructWith(array('value'));
    }

    /**
     * @test
     */
    public function providesDslForSpecifyingMethodValues()
    {
        $this->ogc
            ->expects($this->once())
            ->method('inject')
            ->with('Foo', 'doX', array('value'));

        $this->dsl->given('Foo')->andMethod('doX')->addParam('value');
    }

    /**
     * @test
     */
    public function setsSingleton()
    {
        $this->ogc
            ->expects($this->once())
            ->method('setSingleton')
            ->with('Foo');

        $this->dsl->setSingleton('Foo');
    }
}
