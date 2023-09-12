<?php
namespace ITRocks\Reflect\Interfaces;

use ReflectionException;

/**
 * An interface for all reflection classes
 *
 * @template T of object
 */
interface Reflection_Class extends Reflection
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Inspector bug
	 * @param class-string<T>|T $object_or_class
	 */
	public function __construct(object|string $object_or_class);

	//----------------------------------------------------------------------------------- getConstant
	/** Gets defined constant value */
	public function getConstant(string $name) : mixed;

	//---------------------------------------------------------------------------------- getConstants
	/** @return array<string,mixed> */
	public function getConstants(int $filter = null) : array;

	//-------------------------------------------------------------------------------- getConstructor
	/** Gets the constructor of the reflected class */
	public function getConstructor() : ?Reflection_Method;

	//-------------------------------------------------------------------------- getDefaultProperties
	/** @return array<string,mixed> Gets default value of properties */
	public function getDefaultProperties() : array;

	//----------------------------------------------------------------------------- getImplementNames
	/** @return list<class-string> */
	public function getImplementNames() : array;

	//--------------------------------------------------------------------------------- getImplements
	/** @return array<class-string,static> */
	public function getImplements() : array;

	//----------------------------------------------------------------------------- getInterfaceNames
	/** @return list<class-string> */
	public function getInterfaceNames(int $filter = T_EXTENDS) : array;

	//--------------------------------------------------------------------------------- getInterfaces
	/** @return array<class-string,static> */
	public function getInterfaces(int $filter = T_EXTENDS) : array;

	//------------------------------------------------------------------------------------- getMethod
	/** @throws ReflectionException */
	public function getMethod(string $name) : Reflection_Method;

	//------------------------------------------------------------------------------------ getMethods
	/** @return array<string,Reflection_Method> */
	public function getMethods(int $filter = null) : array;

	//------------------------------------------------------------------------------ getNamespaceName
	public function getNamespaceName() : string;

	//-------------------------------------------------------------------------------- getParentClass
	public function getParentClass() : static|false;

	//--------------------------------------------------------------------------------- getProperties
	/** @return array<string,Reflection_Property> */
	public function getProperties(int $filter = null) : array;

	//----------------------------------------------------------------------------------- getProperty
	/** @throws ReflectionException */
	public function getProperty(string $name) : Reflection_Property;

	//--------------------------------------------------------------------------------- getTraitNames
	/** @return list<class-string> */
	public function getTraitNames(int $filter = 0) : array;

	//------------------------------------------------------------------------------------- getTraits
	/** @return array<class-string,static> */
	public function getTraits(int $filter = 0) : array;

	//----------------------------------------------------------------------------------- inNamespace
	public function inNamespace() : bool;

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param class-string $name
	 */
	public function isA(string $name, ?int $filter = null) : bool;

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract(bool $interface_trait_is_abstract = false) : bool;

	//--------------------------------------------------------------------------------------- isClass
	/** Checks if this class is a class (not an interface or a trait) */
	public function isClass() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInstance
	public function isInstance(object $object) : bool;

	//----------------------------------------------------------------------------------- isInterface
	public function isInterface() : bool;

	//------------------------------------------------------------------------------------ isInternal
	/** Checks if class is defined internally by an extension, or the core */
	public function isInternal() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	/** Checks if user defined */
	public function isUserDefined() : bool;

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param class-string|object $object_or_class
	 */
	public static function of(object|string $object_or_class) : static;

}
