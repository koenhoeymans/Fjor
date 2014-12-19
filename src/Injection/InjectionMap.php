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

    /**
     * @return array
     */
    public function getMethods()
    {
        return array_keys($this->injections);
    }

    /**
     * @param string $method
     *
     * @return array An array of arrays with parameters.
     */
    public function getParams($method)
    {
        return isset($this->injections[$method]) ?
            $this->injections[$method] : array(array());
    }

    /**
     * Parameters to inject for a given method. May be called
     * more than once.
     *
     * @param string $method
     * @param array  $params
     */
    public function add($method, array $params)
    {
        $this->injections[$method][] = $params;

        return $this;
    }

    /**
     * Adds injections from a map to this one and returns a new combined one.
     *
     * @param InjectionMap $map
     *
     * @return InjectionMap A new InjectionMap.
     */
    public function combine(InjectionMap $map)
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
