<?php
namespace ITRocks\Reflect\Interface;

/** @template Class of object */
interface Reflection_Class_Component extends Reflection
{

	public const T_ALL      = self::T_INHERIT | self::T_OVERRIDE;
	public const T_OVERRIDE = 8192;

	//------------------------------------------------------------------------- getAttributeInstances
	public function getAttributeInstances(string $name = null, int $flags = self::T_LOCAL) : array;

	//--------------------------------------------------------------------------------- getAttributes
	public function getAttributes(string $name = null, int $flags = self::T_LOCAL) : array;

	//----------------------------------------------------------------------------- getDeclaringClass
	/** @return Reflection_Class<object> */
	public function getDeclaringClass(bool $trait = false) : Reflection_Class;

	//------------------------------------------------------------------------- getDeclaringClassName
	/** @return class-string */
	public function getDeclaringClassName(bool $trait = false) : string;

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * @return Reflection_Class<Class> Class where the property came from with a call to get...()
	 */
	public function getFinalClass() : Reflection_Class;

	//----------------------------------------------------------------------------- getFinalClassName
	/** @return class-string<Class> Class where the property came from with a call to get...() */
	public function getFinalClassName() : string;

	//------------------------------------------------------------------------------------- isPrivate
	public function isPrivate() : bool;

	//----------------------------------------------------------------------------------- isProtected
	public function isProtected() : bool;

	//-------------------------------------------------------------------------------------- isPublic
	public function isPublic() : bool;

}
