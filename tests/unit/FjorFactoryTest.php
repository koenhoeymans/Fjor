<?php

namespace Fjor;

class FjorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createsDefaultInstance()
    {
        $eventDispatcher = \Epa\EventDispatcherFactory::create();
        $fjorDsl = new \Fjor\FjorDsl(
            new \Fjor\ObjectGraphConstructor(
                new \Fjor\ObjectFactory\GenericObjectFactory(),
                $eventDispatcher
            ),
            $eventDispatcher
        );

        $this->assertEquals(\Fjor\FjorFactory::create(), $fjorDsl);
    }
}
