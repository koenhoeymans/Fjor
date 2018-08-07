<?php

namespace Fjor\Injection;

class InjectionMap
{
    /**
     * $injections = array(
     *     $method => array(
     *         array($firstParamsToInject),
     *         array($secondParamsToInject)
     *     )
     * );
     *
     * @var array
     */
    private $injections = array();

    public function getMethods() : array
    {
        return array_keys($this->injections);
    }

    /**
     * @return array An array of arrays with parameters.
     */
    public function getParams(string $method) : array
    {
        return isset($this->injections[$method]) ?
            $this->injections[$method] : array(array());
    }

    /**
     * Parameters to inject for a given method. May be called
     * more than once.
     */
    public function add(string $method, array $params) : InjectionMap
    {
        $this->injections[$method][] = $params;

        return $this;
    }

    /**
     * Adds injections from a map to this one and returns a new combined one.
     */
    public function combine(InjectionMap $map) : InjectionMap
    {
        $combinedMap = clone $this;
        $methods = $map->getMethods();

        foreach ($methods as $method) {
            $parameterCombinations = $map->getParams($method);
            foreach ($parameterCombinations as $parameterCombination) {
                $combinedMap->add($method, $parameterCombination);
            }
        }

        return $combinedMap;
    }
}
