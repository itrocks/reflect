<?php
namespace ITRocks\Reflect\Interface;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/** @template Class of object */
interface Reflection_Class extends Reflection
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Inspector bug?
	 * @param class-string<Class>|Class $object_or_class
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class);

	//----------------------------------------------------------------------------------- getConstant
	public function getConstant(string $name) : mixed;

	//---------------------------------------------------------------------------------- getConstants
	/**
	 * @param ?int-mask-of<self::T_*|ReflectionClassConstant::IS_*> $filter
	 * @return array<string,mixed>
	 */
	public function getConstants(?int $filter = self::T_INHERIT) : array;

	//-------------------------------------------------------------------------------- getConstructor
	/** @return ?Reflection_Method<Class> */
	public function getConstructor() : ?Reflection_Method;

	//-------------------------------------------------------------------------- getDefaultProperties
	/** @return array<string,mixed> */
	public function getDefaultProperties() : array;

	//----------------------------------------------------------------------------- getInterfaceNames
	/**
	 * @param int-mask-of<self::T_*> $filter
	 * @return list<class-string>
	 */
	public function getInterfaceNames(int $filter = self::T_EXTENDS | self::T_IMPLEMENTS)
		: array;

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @param int-mask-of<self::T_*> $filter
	 * @return array<class-string<object>,Reflection_Class<object>>
	 */
	public function getInterfaces(int $filter = self::T_EXTENDS | self::T_IMPLEMENTS)
		: array;

	//------------------------------------------------------------------------------------- getMethod
	/**
	 * @return Reflection_Method<Class>
	 * @throws ReflectionException
	 */
	public function getMethod(string $name) : Reflection_Method;

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @param ?int-mask-of<self::T_*|ReflectionMethod::IS_*> $filter
	 * @return array<string,Reflection_Method<Class>>
	 */
	public function getMethods(?int $filter = self::T_INHERIT)
		: array;

	//--------------------------------------------------------------------------------------- getName
	/** @return class-string<Class> */
	public function getName() : string;

	//------------------------------------------------------------------------------ getNamespaceName
	public function getNamespaceName() : string;

	//-------------------------------------------------------------------------------- getParentClass
	public function getParentClass() : static|false;

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param ?int-mask-of<self::T_*|ReflectionProperty::IS_*> $filter
	 * @return array<string,Reflection_Property<Class>>
	 */
	public function getProperties(?int $filter = self::T_EXTENDS | self::T_USE) : array;

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @return Reflection_Property<Class>
	 * @throws ReflectionException
	 */
	public function getProperty(string $name) : Reflection_Property;

	//------------------------------------------------------------------------- getReflectionConstant
	/**
	 * @return Reflection_Class_Constant<Class>
	 * @throws ReflectionException
	 */
	public function getReflectionConstant(string $name) : Reflection_Class_Constant;

	//------------------------------------------------------------------------ getReflectionConstants
	/**
	 * @param ?int-mask-of<self::T_*|ReflectionClassConstant::IS_*> $filter
	 * @return array<string,Reflection_Class_Constant<Class>>
	 */
	public function getReflectionConstants(?int $filter = self::T_INHERIT) : array;

	//--------------------------------------------------------------------------------- getTraitNames
	/**
	 * @param int-mask-of<self::T_*|ReflectionClass::IS_*> $filter
	 * @return list<class-string>
	 */
	public function getTraitNames(int $filter = self::T_LOCAL) : array;

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * @param int-mask-of<self::T_*|ReflectionClass::IS_*> $filter
	 * @return array<class-string,Reflection_Class<object>>
	 */
	public function getTraits(int $filter = self::T_LOCAL) : array;

	//----------------------------------------------------------------------------------- inNamespace
	public function inNamespace() : bool;

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param class-string           $name
	 * @param int-mask-of<self::T_*> $filter
	 */
	public function isA(string $name, int $filter = self::T_INHERIT) : bool;

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract(bool $interface_trait_is_abstract = false) : bool;

	//--------------------------------------------------------------------------------------- isClass
	/** Checks if this class is a class (neither an interface nor a trait) */
	public function isClass() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInstance
	public function isInstance(object $object) : bool;

	//----------------------------------------------------------------------------------- isInterface
	public function isInterface() : bool;

	//------------------------------------------------------------------------------------ isInternal
	public function isInternal() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool;

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return self<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function of(object|string $object_or_class) : self;

	//------------------------------------------------------------------------------------------ path
	/** @return class-string<Class> */
	public function path() : string;

}
