<?php

namespace Fjor\Api;

/**
 * Provides access to the dependency injection system.
 */
interface ObjectGraphConstructor
{
    public function addPlugin(\Epa\Api\Plugin $plugin) : void;

    /**
     * @return mixed
     */
    public function get(string $classOrInterface);

    public function given(string $classOrInterface) : \Fjor\Api\Dsl\GivenClassOrInterface\ClassOrInterfaceBindings;

    public function setSingleton(string $classOrInterface) : void;
}
