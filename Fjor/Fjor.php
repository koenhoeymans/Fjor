<?php

/**
 * @package Fjor
 */
namespace Fjor;

use Fjor\ObjectFactory\ObjectFactory;
use Fjor\Injection\InjectionMap;

/**
 * A dependency injection system.
 * 
 * @package Fjor
 */
class Fjor
{
	private $factory;

	/**
	 * array($name => $instance)
	 * 
	 * @var array
	 */
	private $instances = array();

	/**
	 * array('class/interface' => array(
	 * 			'to'		=> $class,
	 * 			'factory'	=> $factory
	 * ));
	 * 
	 * @var array
	 */
	private $bindings = array();

	/**
	 * array($class => InjectionMap);
	 * 
	 * @var array
	 */
	private $injections = array();

	public function __construct(ObjectFactory $defaultFactory)
	{
		$this->factory = $defaultFactory;
	}

	public function addBinding(
		$interfaceOrClass, $toClassOrInstance, ObjectFactory $factory = null
	) {
		$name = $this->normalize($interfaceOrClass);

		if (is_object($toClassOrInstance))
		{
			$this->setInstance($name, $toClassOrInstance);
		}
		else
		{
			$toClassOrInstance = $this->normalize($toClassOrInstance);
			$factory = $factory ?: $this->factory;
			$this->bindings[$name] = array(
				'to' => $toClassOrInstance,
				'factory' => $factory
			);
		}
	}

	public function setSingleton($interfaceOrClass)
	{
		$interfaceOrClass = $this->normalize($interfaceOrClass);
		$this->setInstance($interfaceOrClass, true);
	}

	public function getFactory($class)
	{
		$class = $this->normalize($class);
		return (isset($this->bindings[$class])) ?
			$this->bindings[$class]['factory'] :
			null;
	}

	public function get($classOrInterface)
	{
		$classOrInterface = $this->normalize($classOrInterface);

		$instance = $this->getInstance($classOrInterface);

		if (is_object($instance))
		{
			return $instance;
		}

		if (class_exists($classOrInterface))
		{
			$obj = $this->createClassInstance($classOrInterface);
		}
		elseif (interface_exists($classOrInterface))
		{
			$obj = $this->getImplementation($classOrInterface);
		}
		else
		{
			throw new \Exception(
				'Interface or Class "' . $classOrInterface . '" does not seem to exist.'
			);
		}

		if ($instance)
		{
			$this->setInstance($classOrInterface, $obj);
		}

		return $obj;
	}

	private function createClassInstance($class)
	{
		if (!isset($this->bindings[$class]))
		{
			$this->bindings[$class] = array(
				'to' => $class,
				'factory' => $this->factory
			);
		}

		return $this->bindings[$class]['factory']
				->createInstance($class, $this->getInjectionMap($class), $this);
	}

	private function getImplementation($interface)
	{
		if (!isset($this->bindings[$interface]))
		{
			throw new \Exception('No binding specified for ' . $interface);
		}

		return $this->bindings[$interface]['factory']->createInstance(
			$this->bindings[$interface]['to'],$this->getInjectionMap($interface), $this
		);
	}

	private function normalize($name)
	{
		return ($name[0] === '\\') ? $name : '\\' . $name;
	}

	/**
	 * Set the values for a given method for a class. These values will be used
	 * upon instantation (or after the object is created).
	 * 
	 * @param array $values
	 * @param string $method
	 * @param string $className
	 */
	public function inject($class, $method, array $values)
	{
		$class = $this->normalize($class);
		$this->getInjectionMap($class)->add($method, $values);
	}

	private function createNewInjectionMap($class)
	{
		$this->injections[$class] = new InjectionMap();
	}

	private function getInjectionMap($class)
	{
		if (!isset($this->injections[$class]))
		{
			$this->createNewInjectionMap($class);
		}

		return $this->injections[$class];
	}

	private function setInstance($key, $value)
	{
		$this->instances[$key] = $value;
	}

	private function getInstance($key)
	{
		return isset($this->instances[$key]) ? $this->instances[$key] : null;
	}
}