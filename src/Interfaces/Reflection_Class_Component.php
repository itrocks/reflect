<?php
namespace ITRocks\Reflect\Interfaces;

/**
 * An interface for all reflection class component
 */
interface Reflection_Class_Component extends Reflection
{

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property.
	 * If the property has been declared into a trait, returns the class that uses this trait.
	 *
	 * @return Reflection_Class<object>
	 */
	public function getDeclaringClass() : Reflection_Class;

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property.
	 * If the property has been declared into a trait, returns the name of the class using the trait.
	 *
	 * @return class-string
	 */
	public function getDeclaringClassName() : string;

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Returns The class or trait in which the property was actually declared
	 *
	 * @return Reflection_Class<object>
	 */
	public function getDeclaringTrait() : Reflection_Class;

	//------------------------------------------------------------------------- getDeclaringTraitName
	/** @return class-string The class or trait in which the property was actually declared */
	public function getDeclaringTraitName() : string;

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * @return Reflection_Class<object> The one where the property came from with a call to get...()
	 */
	public function getFinalClass() : Reflection_Class;

	//----------------------------------------------------------------------------- getFinalClassName
	/** @return class-string The one where the property came from with a call to get...() */
	public function getFinalClassName() : string;

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static;

	//------------------------------------------------------------------------------------- isPrivate
	public function isPrivate() : bool;

	//----------------------------------------------------------------------------------- isProtected
	public function isProtected() : bool;

	//-------------------------------------------------------------------------------------- isPublic
	public function isPublic() : bool;

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool;

}
