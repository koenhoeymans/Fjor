<?php

/**
 * @package Fjor
 */
namespace Fjor;

use Epa\Pluggable;
use Epa\Observable;
use Fjor\ObjectFactory\ObjectFactory;
use Fjor\Injection\InjectionMap;

/**
 * A dependency injection system.
 * 
 * @package Fjor
 */
class Fjor implements Observable
{
	use Pluggable;

	private $factory;

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

	/**
	 * Utility method to create an instance of Fjor with the dsl
	 * and one point access to register plugins.
	 * 
	 * @return Fjor
	 */
	public static function defaultSetup()
	{
		$factory = new \Fjor\ObjectFactory\GenericObjectFactory();
		$eventDispatcher = new \Epa\EventDispatcher();
		$fjor = new \Fjor\Dsl\PluggableDsl($factory, $eventDispatcher);
		$fjor->addObserver($eventDispatcher);
		$fjor->given('\\Epa\\EventDispatcher')->thenUse($eventDispatcher);

		return $fjor;
	}

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

	public function setSingleton($interfaceOrClass)
	{
		$interfaceOrClass = $this->normalize($interfaceOrClass);
		$this->addSingleton($interfaceOrClass, true);
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

		$this->notify(new \Fjor\Events\AfterNew($classOrInterface, $obj));

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

		return $this->getFactory($class)->createInstance(
			$class, $this->getCombinedInjectionMap($class), $this
		);
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