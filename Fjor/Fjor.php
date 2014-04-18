<?php

namespace Fjor;

use Epa\Api\EventDispatcher;
use Fjor\ObjectFactory\ObjectFactory;
use Fjor\Injection\InjectionMap;

/**
 * Construct an object graph using dependency injection.
 */
class Fjor
{
	private $factory;

	private $eventDispatcher;

	/**
	 * A list of interfaces or classes that should be singletons.
	 * 
	 * @var array
	 */
	private $singletons = array();

	/**
	 * array('interface|class' => $instance')
	 * 
	 * @var array
	 */
	private $instances = array();

	/**
	 * array('class/interface' => $class);
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

	public function __construct(
		ObjectFactory $factory, EventDispatcher $eventDispatcher
	) {
		$this->factory = $factory;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * Specifies wich class or instance should be used when an interface or
	 * class is encountered.
	 * 
	 * @param string $classOrInterface
	 * @param mixed $toClassOrInstance
	 * 
	 * @return void
	 */
	public function addBinding($classOrInterface, $toClassOrInstance)
	{
		$classOrInterface = $this->normalize($classOrInterface);

		if (is_object($toClassOrInstance))
		{
			$this->addSingletonInstance($classOrInterface, $toClassOrInstance);
		}
		else
		{
			$toClassOrInstance = $this->normalize($toClassOrInstance);
			$this->bindings[$classOrInterface] = $toClassOrInstance;
		}
	}

	/**
	 * Set an interface or class as singleton. The same object will be used
	 * every time the interface or class is encountered.
	 * 
	 * @param string $classOrInterface
	 * 
	 * @return void
	 */
	public function setSingleton($classOrInterface)
	{
		$this->addToListOfSingletons($this->normalize($classOrInterface));
	}

	/**
	 * Get an instance of a class or interface.
	 * 
	 * @param string $classOrInterface
	 * 
	 * @return mixed
	 */
	public function getInstance($classOrInterface)
	{
		$classOrInterface = $this->normalize($classOrInterface);

		$obj = $this->getSingletonInstance($classOrInterface);

		if (!$obj)
		{
			$obj = $this->createObject($classOrInterface);
		}

		if ($this->isSingleton($classOrInterface))
		{
			$this->addSingletonInstance($classOrInterface, $obj);
		}

		return $obj;
	}

	private function createObject($classOrInterface)
	{
		if (class_exists($classOrInterface))
		{
			$obj = $this->createClassInstance($classOrInterface);
		}
		elseif (interface_exists($classOrInterface))
		{
			$obj = $this->getInterfaceImplementation($classOrInterface);
		}
		else
		{
			throw new \Exception(
				'Interface or Class "' . $classOrInterface . '" does not seem to exist.'
			);
		}

		return $obj;
	}

	private function createClassInstance($class)
	{
		if (!isset($this->bindings[$class]))
		{
			$this->bindings[$class] = $class;
		}

		$obj = $this->factory->createInstance(
			$class, $this->getCombinedInjectionMap($class), $this
		);

		$this->eventDispatcher->notify(new \Fjor\Events\AfterNew($class, $obj));

		return $obj;
	}

	private function getInterfaceImplementation($interface)
	{
		if (!isset($this->bindings[$interface]))
		{
			throw new \Exception('No binding specified for ' . $interface);
		}

		return $this->getInstance($this->bindings[$interface]);
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
	 * 
	 * @return void
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

	private function hasInjectionMap($class)
	{
		return isset($this->injections[$class]);
	}

	private function getInjectionMap($class)
	{
		if (!isset($this->injections[$class]))
		{
			$this->createNewInjectionMap($class);
		}

		return $this->injections[$class];
	}

	private function getCombinedInjectionMap($class)
	{
		$map = $this->getInjectionMap($class);

		foreach (class_implements($class) as $implementation)
		{
			$implementation = $this->normalize($implementation);
			if (!$this->hasInjectionMap($implementation))
			{
				continue;
			}
			$map = $map->combine($this->getInjectionMap($implementation));
		}

		foreach ($this->getParentClasses($class) as $parentClass)
		{
			$parentClass = $this->normalize($parentClass);
			if (!$this->hasInjectionMap($parentClass))
			{
				continue;
			}
			$map = $map->combine($this->getInjectionMap($parentClass));
		}

		return $map;
	}

	private function getParentClasses($class)
	{
		$parentClasses = array();
		while ($class = get_parent_class($class))
		{
			$parentClasses[] = $class;
		}

		return $parentClasses;
	}

	private function addToListOfSingletons($classOrInterface)
	{
		if (!in_array($classOrInterface, $this->singletons))
		{
			$this->singletons[$classOrInterface] = $classOrInterface;
		}
	}

	private function isSingleton($classOrInterface)
	{
		return in_array($classOrInterface, $this->singletons);
	}

	private function getSingletonInstance($classOrInterface)
	{
		if (isset($this->instances[$classOrInterface]))
		{
			return $this->instances[$classOrInterface];
		}
	}

	private function addSingletonInstance($classOrInterface, $instance)
	{
		$this->instances[$classOrInterface] = $instance;
	}
}