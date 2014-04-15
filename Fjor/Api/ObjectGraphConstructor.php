<?php

namespace Fjor\Api;

/**
 * Provides access to the dependency injection system.
 */
interface ObjectGraphConstructor
{
	/**
	 * @param \Epa\Api\Plugin $plugin
	 * 
	 * @return void
	 */
	public function addPlugin(\Epa\Api\Plugin $plugin);

	/**
	 * @param string $classOrInterface
	 * 
	 * @return mixed
	 */
	public function get($classOrInterface);

	/**
	 * @param string $classOrInterface
	 * 
	 * @return \Fjor\Api\Dsl\GivenClassOrInterface\ClassOrInterfaceBindings
	 */
	public function given($classOrInterface);

	/**
	 * @param string $classOrInterface
	 * 
	 * @return void
	 */
	public function setSingleton($classOrInterface);
}