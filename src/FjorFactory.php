<?php

namespace Fjor;

class FjorFactory
{
    /**
     * Creates a default setup for Fjor.
     */
    public static function create() : \Fjor\Api\ObjectGraphConstructor
    {
        $eventDispatcher = \Epa\EventDispatcherFactory::create();
        $fjorDsl = new \Fjor\FjorDsl(
            new \Fjor\ObjectGraphConstructor(
                new \Fjor\ObjectFactory\GenericObjectFactory(),
                $eventDispatcher
            ),
            $eventDispatcher
        );

        return $fjorDsl;
    }
}
