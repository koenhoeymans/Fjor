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
	 * array($name => $instance|true)
	 * 
	 * `$instance` is an object if one already created, `true`
	 * when the bound class or interface should be a singletone but
	 * no implementation is created yet
	 * 
	 * @var array
	 */
	private $singleton = array();

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

	public function __construct(
		ObjectFactory $defaultFactory, EventDispatcher $eventDispatcher
	) {
		$this->factory = $defaultFactory;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * Specifies wich class or instance should be used when an interface or
	 * class is encountered. Optionally a factory can be specified that will
	 * create an instance.
	 * 
	 * @param string $interfaceOrClass
	 * @param mixed $toClassOrInstance
	 * @param ObjectFactory $factory
	 * 
	 * @return void
	 */
	public function addBinding(
		$interfaceOrClass, $toClassOrInstance, ObjectFactory $factory = null
	) {
		$name = $this->normalize($interfaceOrClass);

		if (is_object($toClassOrInstance))
		{
			$this->addSingleton($name, $toClassOrInstance);
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

	/**
	 * Set an interface or class as singleton. The same object will be used
	 * every time the interface or class is encountered.
	 * 
	 * @param string $interfaceOrClass
	 * 
	 * @return void
	 */
	public function setSingleton($interfaceOrClass)
	{
		$interfaceOrClass = $this->normalize($interfaceOrClass);
		$this->addSingleton($interfaceOrClass, true);
	}

	/**
	 * Return the factory for creating an instance of the class or null
	 * if there is none.
	 * 
	 * @param string $class
	 * 
	 * @return mixed|null
	 */
	public function getFactory($class)
	{
		$class = $this->normalize($class);
		return (isset($this->bindings[$class])) ?
			$this->bindings[$class]['factory'] :
			null;
	}

	/**
	 * Get an instance of a class or interface.
	 * 
	 * @param string $classOrInterface
	 * 
	 * @return mixed
	 */
	public function get($classOrInterface)
	{
		$classOrInterface = $this->normalize($classOrInterface);

		$singleton = $this->getSingleton($classOrInterface);

		if (is_object($singleton))
		{
			$obj = $singleton;
		}
		else
		{
			$obj = $this->getObject($classOrInterface);

			if ($singleton)
			{
				$this->addSingleton($classOrInterface, $obj);
			}
		}

		return $obj;
	}

	private function getObject($classOrInterface)
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
			$this->bindings[$class] = array(
				'to' => $class,
				'factory' => $this->factory
			);
		}

		$obj = $this->getFactory($class)->createInstance(
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

		return $this->get($this->bindings[$interface]['to']);
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

	private function addSingleton($key, $value)
	{
		$this->singleton[$key] = $value;
	}

	private function getSingleton($key)
	{
		return isset($this->singleton[$key]) ? $this->singleton[$key] : null;
	}
}