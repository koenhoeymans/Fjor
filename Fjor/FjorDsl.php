<?php

namespace Fjor;

use Fjor\Fjor;
use Fjor\Api\ObjectGraphConstructor;
use Fjor\Api\Dsl\GivenClassOrInterface\ClassOrInterfaceBindings;
use Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam;
use Fjor\ObjectFactory\ObjectFactory;
use Epa\EventDispatcher;
use Epa\Plugin;

/**
 * Provides a higher level DSL for Fjor.
 */
class FjorDsl
	extends Fjor
	implements ObjectGraphConstructor, ClassOrInterfaceBindings, AddParam
{
	private $eventDispatcher;

	private $given;

	private $thenUse;

	private $method;

	public function __construct(
		ObjectFactory $defaultFactory, EventDispatcher $eventDispatcher
	) {
		$this->eventDispatcher = $eventDispatcher;
		parent::__construct($defaultFactory);
	}

	/**
	 * @see \Fjor\Api\ObjectConstructor::registerPlugin()
	 */
	public function registerPlugin(Plugin $plugin)
	{
		$this->eventDispatcher->registerPlugin($plugin);
	}

	/**
	 * @see \Fjor\Api\ObjectConstructor::given()
	 */
	public function given($classOrInterface)
	{
		$this->given = $classOrInterface;
		$this->thenUse = null;
		$this->method = null;

		return $this;
	}

	/**
	 * @see \Fjor\Api\Dsl\GivenClassOrInterface\ThenUse::thenUse()
	 */
	public function thenUse($classOrInterfaceOrFactoryOrClosure)
	{
		$this->thenUse = $classOrInterfaceOrFactoryOrClosure;

		$this->addBinding($this->given,	$classOrInterfaceOrFactoryOrClosure);

		if (!is_object($classOrInterfaceOrFactoryOrClosure))
		{
			return $this;
		}
	}

	/**
	 * @see \Fjor\Api\Dsl\GivenClassOrInterface\ConstructWith::constructWith()
	 */
	public function constructWith(array $values)
	{
		$target = ($this->thenUse === null) ? $this->given : $this->thenUse;
		$this->inject($target, '__construct', $values);
	}

	/**
	 * @see \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod::andMethod()
	 */
	public function andMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @see \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam::addParam()
	 */
	public function addParam(array $values = array())
	{
		$this->inject($this->given, $this->method, $values);
		return $this;
	}
}