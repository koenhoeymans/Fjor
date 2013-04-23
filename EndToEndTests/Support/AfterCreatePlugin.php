<?php

/**
 * @package Fjor
 */
namespace Fjor\EndToEndTests\Support;

use Fjor\Events\AfterNew;

use Epa\EventMapper;
use Epa\Plugin;

/**
 * @package Fjor
 */
class AfterCreatePlugin extends Plugin
{
	private $called = array();

	public function register(EventMapper $mapper)
	{
		$mapper->registerForEvent(
			'Fjor\\Events\\AfterNew',
			function(\Fjor\Events\AfterNew $afterNew)
			{
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