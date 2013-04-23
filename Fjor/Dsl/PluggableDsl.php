<?php

/**
 * @package Fjor
 */
namespace Fjor\Dsl;

use Epa\EventDispatcher;

use Fjor\ObjectFactory\ObjectFactory;

use Epa\Plugin;

/**
 * @package
 */
class PluggableDsl extends Dsl
{
	private $eventDispatcher;

	public function __construct(
		ObjectFactory $defaultFactory, EventDispatcher $eventDispatcher
	) {
		$this->eventDispatcher = $eventDispatcher;
		parent::__construct($defaultFactory);
	}

	public function registerPlugin(Plugin $plugin)
	{
		$this->eventDispatcher->registerPlugin($plugin);
	}
}