<?php
namespace ITRocks\Reflect\Interfaces;

use ITRocks\Reflect\Type;
use ReflectionException;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Property extends Reflection_Class_Component
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param class-string|object $class_name
	 * @throws ReflectionException
	 */
	public function __construct(object|string $class_name, string $property_name);

	//--------------------------------------------------------------------------------------- getType
	public function getType() : ?Type;

	//-------------------------------------------------------------------------------------- getValue
	public function getValue(object $object) : mixed;

	//-------------------------------------------------------------------------------------------- is
	public function is(Reflection_Property $property) : bool;

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool;

}
