<?php

namespace Fjor;

use Fjor\Api\ObjectGraphConstructor as ApiObjectGraphConstructor;
use Fjor\Api\Dsl\GivenClassOrInterface\ClassOrInterfaceBindings;
use Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam;
use Epa\Api\EventDispatcher;
use Epa\Api\Plugin;

/**
 * Provides a higher level DSL for Fjor.
 */
class FjorDsl implements
    ApiObjectGraphConstructor,
    ClassOrInterfaceBindings,
    AddParam
{
    private $ogc;

    private $eventDispatcher;

    private $given;

    private $method;

    public function __construct(
        ObjectGraphConstructor $objectGraphConstructor,
        EventDispatcher $eventDispatcher
    ) {
        $this->ogc = $objectGraphConstructor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @see \Fjor\Api\ObjectGraphConstructor::addPlugin()
     */
    public function addPlugin(Plugin $plugin) : void
    {
        $this->eventDispatcher->addPlugin($plugin);
    }

    /**
     * @see \Fjor\Api\ObjectGraphConstructor::get()
     */
    public function get(string $classOrInterface)
    {
        return $this->ogc->getInstance($classOrInterface);
    }

    /**
     * @see \Fjor\Api\ObjectGraphConstructor::given()
     */
    public function given(string $classOrInterface) : \Fjor\Api\Dsl\GivenClassOrInterface\ClassOrInterfaceBindings
    {
        $this->given = $classOrInterface;
        $this->method = null;

        return $this;
    }

    /**
     * @see \Fjor\Api\Dsl\GivenClassOrInterface\ThenUse::thenUse()
     */
    public function thenUse($classOrInterfaceOrFactoryOrClosure) : void
    {
        $this->ogc->addBinding($this->given, $classOrInterfaceOrFactoryOrClosure);
    }

    /**
     * @see \Fjor\Api\Dsl\GivenClassOrInterface\ConstructWith::constructWith()
     */
    public function constructWith(array $values) : void
    {
        $this->ogc->inject($this->given, '__construct', $values);
    }

    /**
     * @see \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod::andMethod()
     */
    public function andMethod(string $method) : \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @see \Fjor\Api\Dsl\GivenClassOrInterface\AndMethod\AddParam::addParam()
     */
    public function addParam(...$values) : addParam
    {

        $this->ogc->inject($this->given, $this->method, $values);

        return $this;
    }

    /**
     * @see \Fjor\Api\ObjectGraphConstructor::setSingleton()
     */
    public function setSingleton(string $classOrInterface) : void
    {
        $this->ogc->setSingleton($classOrInterface);
    }
}
