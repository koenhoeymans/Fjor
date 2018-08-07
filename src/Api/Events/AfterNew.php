<?php

namespace Fjor\Api\Events;

/**
 * This event is thrown after a new object has been created.
 */
interface AfterNew
{
    /**
     * Get the class that was instantiated.
     */
    public function getClass() : string;

    /**
     * Get the object that was created.
     *
     * @return mixed
     */
    public function getObject();
}
