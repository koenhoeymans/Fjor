<?php

namespace Fjor\Api\Events;

/**
 * This event is thrown after a new object has been created.
 */
interface AfterNew
{
    /**
     * Get the class that was instantiated.
     *
     * @return string
     */
    public function getClass();

    /**
     * Get the object that was created.
     *
     * @return mixed
     */
    public function getObject();
}
