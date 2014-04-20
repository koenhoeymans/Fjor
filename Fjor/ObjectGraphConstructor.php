<?php

namespace Fjor;

use Epa\Api\EventDispatcher;
use Fjor\ObjectFactory\ObjectFactory;
use Fjor\Injection\InjectionMap;

/**
 * Construct an object graph using dependency injection.
 */
class ObjectGraphConstructor
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
		return ($name[0] === '\\') ? substr($name, 1) : $name;
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
		$implements = class_implements($class);
		$parents = class_parents($class);

		$maps = $this->getInjectionMapsForAll(array_merge($implements, $parents));
		$maps[] = $this->getInjectionMap($class);
 
		return $this->combineInjectionMaps($maps);
	}

	private function getInjectionMapsForAll(array $classesAndInterfaces)
	{
		$maps = array();
		foreach ($classesAndInterfaces as $classOrInterface)
		{
			$map = $this->getInjectionMap($classOrInterface);
			if ($map)
			{
				$maps[] = $map;
			}
		}

		return $maps;
	}

	private function combineInjectionMaps(array $maps)
	{
		$newMap = array_shift($maps);
		foreach ($maps as $map)
		{
			$newMap = $map->combine($newMap);
		}

		return $newMap;
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