<?php

namespace Fjor\ObjectFactory;

Use Fjor\ObjectGraphConstructor;
Use Fjor\Injection\InjectionMap;

class GenericObjectFactory implements ObjectFactory
{
	private $ogc;

	public function createInstance(
		$class,
		InjectionMap $injectionMap,
		ObjectGraphConstructor $ogc
	) {
		$this->ogc = $ogc;

		$obj = $this->createObject($class, $injectionMap);
		$this->injectMethodDependencies($obj, $injectionMap);

		return $obj;
	}

	private function createObject($className, InjectionMap $injectionMap)
	{
		$reflectionClass = new \ReflectionClass($className);
		if (!$reflectionClass->hasMethod('__construct')) {
			$obj = $reflectionClass->newInstanceArgs();
		}
		else {
			$userSpecifiedArgs = $injectionMap->getParams('__construct')[0];
			$args = $this->getMethodDependencies(
				$userSpecifiedArgs,
				$reflectionClass->getMethod('__construct')
			);
			$obj = $reflectionClass->newInstanceArgs($args);
		}

		return $obj;
	}

	private function injectMethodDependencies($object, InjectionMap $injectionMap)
	{
		$reflectionClass = new \ReflectionClass($object);
		foreach ($injectionMap->getMethods() as $method) {
			if ($method === '__construct') {
				continue;
			}

			$reflectionMethod = $reflectionClass->getMethod($method);
			foreach ($injectionMap->getParams($method) as $userSpecifiedArgs) {
				$params = $this->getMethodDependencies(
					$userSpecifiedArgs,
					$reflectionMethod
				);
				call_user_func_array(array($object, $method), $params);
			}
		}
	}

	private function getMethodDependencies(
		array $userSpecifiedArgs,
		\ReflectionMethod $reflectionMethod
	) {
		foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
			$argumentPosition = $reflectionParameter->getPosition();
			$userSpecifiedValue = isset($userSpecifiedArgs[$argumentPosition])
				? $userSpecifiedArgs[$argumentPosition]
				: null;

			if (is_object($userSpecifiedValue)) {
				continue;
			}
			elseif ($reflectionParameter->getClass()) {
				$paramObj = $this->getObject(
					$userSpecifiedValue,
					$reflectionParameter->getClass()->getName()
				);
			}
			elseif (isset($userSpecifiedValue)) {
				continue;
			}
			elseif ($reflectionParameter->isDefaultValueAvailable()) {
				$paramObj = $reflectionParameter->getDefaultValue();
			}
			elseif ($reflectionParameter->allowsNull()) {
				$paramObj = null;
			}
			elseif ($reflectionParameter->isOptional()) {
				continue;
			}
			else {
				$class = $reflectionMethod->getDeclaringClass()->getName();
				$method = $reflectionMethod->getName();
				$param = $reflectionParameter->getName();
				throw new \Exception(
					'No dependency specified for "' . $class . '::' . $method
					. '" on position ' . $argumentPosition . ', parametername $'
					. $param
				);
			}

			$userSpecifiedArgs[$argumentPosition] = $paramObj;
		}

		ksort($userSpecifiedArgs);

		return $userSpecifiedArgs;
	}

	private function getObject(
		$userGivenClassName = null,
		$paramClassOrInterfaceName
	) {
		if ($userGivenClassName) {
			return $this->ogc->getInstance($userGivenClassName);
		}
		else {
			return $this->ogc->getInstance($paramClassOrInterfaceName);
		}
	}
}