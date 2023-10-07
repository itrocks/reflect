<?php
namespace ITRocks\Reflect\Interface;

use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ReflectionException;

/**
 * @extends Reflection_Class_Component<Class>
 * @template Class of object
 */
interface Reflection_Property extends Reflection_Class_Component
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<Class>|Class $object_or_class
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class, string $property);

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static;

	//--------------------------------------------------------------------------------------- getType
	public function getType() : ?Reflection_Type;

	//-------------------------------------------------------------------------------------- getValue
	public function getValue(object $object) : mixed;

	//-------------------------------------------------------------------------------------------- is
	/** @param Reflection_Property<object> $property */
	public function is(Reflection_Property $property) : bool;

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool;

}
