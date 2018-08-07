<?php

namespace Fjor;

use Fjor\Api\Events\AfterNew;
use Epa\Api\EventDispatcher;
use Epa\Api\Plugin;

class AfterCreatePlugin implements Plugin
{
    private $called = array();

    public function registerHandlers(EventDispatcher $dispatcher) : void
    {
        $dispatcher->registerForEvent(
            'Fjor\\Api\\Events\\AfterNew',
            function (AfterNew $afterNew) {
                $this->callMe($afterNew);
            }
        );
    }

    public function wasCalledAfterCreationOf($class)
    {
        return in_array($class, $this->called);
    }

    public function callMe(AfterNew $event)
    {
        $this->called[] = $event->getClass();
    }
}
