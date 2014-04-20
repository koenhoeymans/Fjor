<?php

namespace Fjor\ObjectFactory;

Use Fjor\ObjectGraphConstructor;
Use Fjor\Injection\InjectionMap;

class GenericObjectFactory implements ObjectFactory
{
	public function createInstance($class, InjectionMap $injections, ObjectGraphConstructor $ogc)
	{	
		## we'll have to create a new instance
		$reflectionClass = new \ReflectionClass($class);
	
		if (!$reflectionClass->hasMethod('__construct'))
		{
			$obj = $reflectionClass->newInstanceArgs();
		}
		else
		{
			$params = $injections->getParams('__construct');
			$params = $this->findMissingDependencies(
				$params[0],
				$reflectionClass->getMethod('__construct'),
				$ogc
			);

			$obj = $reflectionClass->newInstanceArgs($params);
		}

		foreach ($injections->getMethods() as $method)
		{
			if ($method === '__construct')
			{
				continue;
			}

			$reflectionMethod = $reflectionClass->getMethod($method);
			foreach ($injections->getParams($method) as $params)
			{
				$params = $this->findMissingDependencies(
					$params, $reflectionMethod, $ogc
				);
				call_user_func_array(array($obj, $method), $params);
			}
		}

		return $obj;
	}

	private function findMissingDependencies(
		array $params, \ReflectionMethod $reflectionMethod, ObjectGraphConstructor $ogc
	) {
		foreach ($reflectionMethod->getParameters() as $reflectionParameter)
		{
			$argumentPosition = $reflectionParameter->getPosition();
			$paramReflectionClass = $reflectionParameter->getClass();
			$value = isset($params[$argumentPosition]) ?
				$params[$argumentPosition] : null;

			# an object needed
			if ($paramReflectionClass)
			{
				$paramObj = $this->getObject($value, $paramReflectionClass, $ogc);
			}
			# something else needed and it's specified
			elseif (isset($value))
			{
				continue;
			}
			# something else needed, it's not specified but default available
			elseif ($reflectionParameter->isDefaultValueAvailable())
			{
				$paramObj = $reflectionParameter->getDefaultValue();
			}
			# we can get away with `null` as value
			elseif ($reflectionParameter->allowsNull())
			{
				$paramObj = null;
			}
			# a last resort
			# there's no default value for internal functions though the manual
			# often states 'null'. Eg SplObjectStorage::attach($obj, $optional = null)
			# Here we can be saved by using `IsOptional`.
			elseif ($reflectionParameter->isOptional())
			{
				continue;
			}
			else
			{
				$class = $reflectionMethod->getDeclaringClass()->getName();
				$method = $reflectionMethod->getName();
				$param = $reflectionParameter->getName();
				throw new \Exception(
					'No dependency specified for "' . $class . '::' . $method
					. '" on position ' . $argumentPosition . ', parametername $' . $param
				);
			}

			$params[$argumentPosition] = $paramObj;
		}

		ksort($params);

		return $params;
	}

	private function getObject(
		$value, \ReflectionClass $paramReflectionClass, ObjectGraphConstructor $ogc
	) {
		## provided by user
		if ($value)
		{
			### as binding
			if (is_string($value))
			{
				$paramObj = $ogc->getInstance($value);
			}
			### or object
			else
			{
				$paramObj = $value;
			}
		}
		## not provided by user -> get it from Fjor
		else
		{
			$paramClassName = $paramReflectionClass->getName();
			$paramObj = $ogc->getInstance($paramClassName);
		}

		return $paramObj;
	}
}