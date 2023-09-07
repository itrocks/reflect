<?php
namespace ITRocks\Reflect\Interfaces;

use ReflectionException;

/**
 * An interface for all reflection method classes
 */
interface Reflection_Method extends Reflection_Class_Component
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param class-string|object|string $object_or_class_or_method
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class_or_method, string $method = null);

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by "\n".
	 */
	public function getPrototypeString() : string;

	//--------------------------------------------------------------------------- getReturnTypeString
	public function getReturnTypeString() : string;

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

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool;

	//------------------------------------------------------------------------------ returnsReference
	public function returnsReference() : bool;

}
