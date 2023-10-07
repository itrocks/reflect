<?php
namespace ITRocks\Reflect\Interface;

use ITRocks\Reflect\Reflection_Parameter;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ReflectionException;

/**
 * @extends Reflection_Class_Component<Class>
 * @template Class of object
 */
interface Reflection_Method extends Reflection_Class_Component
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class_or_method Argument type does not match the declared
	 * @param class-string<Class>|Class|string $object_or_class_or_method
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class_or_method, string $method = null);

	//--------------------------------------------------------------------------------- getParameters
	/** @return array<string,Reflection_Parameter<Class>> */
	public function getParameters() : array;

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static;

	//---------------------------------------------------------------------------------- getPrototype
	public function getPrototype() : static;

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by "\n".
	 */
	public function getPrototypeString() : string;

	//--------------------------------------------------------------------------------- getReturnType
	public function getReturnType() : Reflection_Type;

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract() : bool;

	//--------------------------------------------------------------------------------- isConstructor
	public function isConstructor() : bool;

	//---------------------------------------------------------------------------------- isDestructor
	public function isDestructor() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInternal
	public function isInternal() : bool;

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool;

	//------------------------------------------------------------------------------ returnsReference
	public function returnsReference() : bool;

}
