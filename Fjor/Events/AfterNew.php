<?php

/**
 * @package Fjor
 */
namespace Fjor\Events;

use Epa\Event;

/**
 * @package Fjor
 */
class AfterNew implements Event
{
	private $class;

	private $object;

	public function __construct($class, $object)
	{
		$this->class = $class;
		$this->object = $object;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getObject()
	{
		return $this->object;
	}
}