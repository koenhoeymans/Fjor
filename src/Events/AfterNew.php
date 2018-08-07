<?php

namespace Fjor\Events;

use Epa\Api\Event;

class AfterNew implements Event, \Fjor\Api\Events\AfterNew
{
    private $class;

    private $object;

    public function __construct(string $class, object $object)
    {
        $this->class = $class;
        $this->object = $object;
    }

    /**
     * @see \Fjor\Api\Events\AfterNew::getClass()
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @see \Fjor\Api\Events\AfterNew::getObject()
     */
    public function getObject()
    {
        return $this->object;
    }
}
