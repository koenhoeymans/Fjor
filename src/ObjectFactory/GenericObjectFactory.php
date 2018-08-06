<?php

namespace Fjor\ObjectFactory;

use Fjor\ObjectGraphConstructor;
use Fjor\Injection\InjectionMap;

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
        } else {
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
            if (!isset($userSpecifiedArgs[$argumentPosition])) {
                # assumes that if no value is specified for an optional parameter
                # without defaults then further parameters are not specified
                # neither.
                if ($reflectionParameter->isOptional()) {
                    return $userSpecifiedArgs;
                }
                $value = $this->findValueForArgument($reflectionParameter);
            } else {
                $value = $this->adaptValueForParam(
                    $userSpecifiedArgs[$argumentPosition],
                    $reflectionParameter
                );
            }

            $userSpecifiedArgs[$argumentPosition] = $value;
        }

        ksort($userSpecifiedArgs);

        return $userSpecifiedArgs;
    }

    private function findValueForArgument(\ReflectionParameter $reflectionParameter)
    {
        if ($reflectionParameter->getClass()) {
            return $this->ogc->getInstance(
                $reflectionParameter->getClass()->getName()
            );
        }
        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }
        if ($reflectionParameter->allowsNull()) {
            return null;
        }
        
        $class = $reflectionParameter->getDeclaringClass()->getName();
        $method = $reflectionParameter->getDeclaringFunction()->getName();
        $argumentPosition = $reflectionParameter->getPosition();
        $paramName = $reflectionParameter->getName();
        throw new \Exception(
            'No dependency specified for "'.$class.'::'.$method
            .'" on position '.$argumentPosition.', parametername $'
            .$paramName
        );
    }

    private function adaptValueForParam(
        $value,
        \ReflectionParameter $reflectionParameter
    ) {
        if (is_object($value)) {
            return $value;
        }
        if ($reflectionParameter->getClass()) {
            return $this->ogc->getInstance($value);
        }

        return $value;
    }
}
