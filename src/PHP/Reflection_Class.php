<?php
namespace ITRocks\Reflect\PHP;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Interface\Reflection_Attribute;
use ITRocks\Reflect\Interface\Reflection_Method;
use ITRocks\Reflect\Interface\Reflection_Parameter;
use ITRocks\Reflect\Interface\Reflection_Property;
use ReflectionException;

/**
 * @implements Interface\Reflection_Class<Class>
 * @template Class of object
 */
class Reflection_Class implements Interface\Reflection_Class
{

	//----------------------------------------------------------------------------------------- $name
	/** @var class-string<Class> */
	public string $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(object|string $object_or_class)
	{
		$this->name = is_object($object_or_class)
			? get_class($object_or_class)
			: $object_or_class;
	}

	//---------------------------------------------------------------------------------- getAttribute
	public function getAttribute(string $name) : ?Reflection_Attribute
	{
		// TODO: Implement getAttribute() method.
		return null;
	}

	//------------------------------------------------------------------------- getAttributeInstances
	public function getAttributeInstances(string $name = null, int $flags = 0) : array
	{
		// TODO: Implement getAttributeInstances() method.
		return [];
	}

	//--------------------------------------------------------------------------------- getAttributes
	public function getAttributes(string $name = null, int $flags = 0) : array
	{
		// TODO: Implement getAttributes() method.
		return [];
	}

	//----------------------------------------------------------------------------------- getConstant
	public function getConstant(string $name) : null
	{
		// TODO: Implement getConstant() method.
		return null;
	}

	//---------------------------------------------------------------------------------- getConstants
	public function getConstants(?int $filter = self::T_INHERIT) : array
	{
		// TODO: Implement getConstants() method.
		return [];
	}

	//-------------------------------------------------------------------------------- getConstructor
	public function getConstructor() : ?Reflection_Method
	{
		// TODO: Implement getConstructor() method.
		return null;
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	public function getDefaultProperties() : array
	{
		// TODO: Implement getDefaultProperties() method.
		return [];
	}

	//--------------------------------------------------------------------------------- getDocComment
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false
	{
		// TODO: Implement getDocComment() method.
		return false;
	}

	//----------------------------------------------------------------------------- getInterfaceNames
	public function getInterfaceNames(
		int $filter = self::T_EXTENDS | self::T_IMPLEMENTS
	) : array
	{
		// TODO: Implement getInterfaceNames() method.
		return [];
	}

	//--------------------------------------------------------------------------------- getInterfaces
	public function getInterfaces(
		int $filter = self::T_EXTENDS | self::T_IMPLEMENTS
	) : array
	{
		// TODO: Implement getInterfaces() method.
		return [];
	}

	//------------------------------------------------------------------------------------- getMethod
	public function getMethod(string $name) : Reflection_Method
	{
		// TODO: Implement getMethod() method.
		throw new ReflectionException('TODO: Implement getMethod() method.');
	}

	//------------------------------------------------------------------------------------ getMethods
	public function getMethods(?int $filter = self::T_INHERIT) : array
	{
		// TODO: Implement getMethods() method.
		return [];
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------ getNamespaceName
	public function getNamespaceName() : string
	{
		return substr($this->name, 0, intval(strrpos($this->name, '\\')));
	}

	//-------------------------------------------------------------------------------- getParentClass
	public function getParentClass() : static|false
	{
		// TODO: Implement getParentClass() method.
		return false;
	}

	//--------------------------------------------------------------------------------- getProperties
	public function getProperties(?int $filter = self::T_EXTENDS | self::T_USE) : array
	{
		// TODO: Implement getProperties() method.
		return [];
	}

	//----------------------------------------------------------------------------------- getProperty
	public function getProperty(string $name) : Reflection_Property
	{
		// TODO: Implement getProperty() method.
		throw new ReflectionException('TODO: Implement getProperty() method.');
	}

	//--------------------------------------------------------------------------------- getTraitNames
	public function getTraitNames(int $filter = self::T_LOCAL) : array
	{
		// TODO: Implement getTraitNames() method.
		return [];
	}

	//------------------------------------------------------------------------------------- getTraits
	public function getTraits(int $filter = self::T_LOCAL) : array
	{
		// TODO: Implement getTraits() method.
		return [];
	}

	//----------------------------------------------------------------------------------- inNamespace
	public function inNamespace() : bool
	{
		return str_contains($this->name, '\\');
	}

	//------------------------------------------------------------------------------------------- isA
	public function isA(string $name, int $filter = self::T_INHERIT) : bool
	{
		// TODO: Implement isA() method.
		return false;
	}

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract(bool $interface_trait_is_abstract = false) : bool
	{
		// TODO: Implement isAbstract() method.
		return false;
	}

	//--------------------------------------------------------------------------------------- isClass
	public function isClass() : bool
	{
		// TODO: Implement isClass() method.
		return false;
	}

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool
	{
		// TODO: Implement isFinal() method.
		return false;
	}

	//------------------------------------------------------------------------------------ isInstance
	public function isInstance(object $object) : bool
	{
		// TODO: Implement isInstance() method.
		return false;
	}

	//----------------------------------------------------------------------------------- isInterface
	public function isInterface() : bool
	{
		// TODO: Implement isInterface() method.
		return false;
	}

	//------------------------------------------------------------------------------------ isInternal
	public function isInternal() : bool
	{
		// TODO: Implement isInternal() method.
		return false;
	}

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool
	{
		// TODO: Implement isUserDefined() method.
		return false;
	}

	//--------------------------------------------------------------------------------- newReflection
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return ($member is null ? Reflection_Class<C> : Reflection_Method<C>|Reflection_Property<C>)
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflection(object|string $object_or_class, string $member = null)
		: Reflection_Class|Reflection_Method|Reflection_Property
	{
		// TODO: Implement newReflection() method.
		throw new ReflectionException('TODO: Implement newReflection() method.');
	}

	//---------------------------------------------------------------------------- newReflectionClass
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Class<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionClass(object|string $object_or_class) : Reflection_Class
	{
		// TODO: Implement newReflectionClass() method.
		throw new ReflectionException('TODO: Implement newReflectionClass() method.');
	}

	//--------------------------------------------------------------------------- newReflectionMethod
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Method<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionMethod(object|string $object_or_class, string $method)
		: Reflection_Method
	{
		// TODO: Implement newReflectionMethod() method.
		throw new ReflectionException('TODO: Implement newReflectionMethod() method.');
	}

	//------------------------------------------------------------------------ newReflectionParameter
	public static function newReflectionParameter(object|array|string $function, int|string $param)
		: Reflection_Parameter
	{
		// TODO: Implement newReflectionParameter() method.
		throw new ReflectionException('TODO: Implement newReflectionParameter() method.');
	}

	//------------------------------------------------------------------------- newReflectionProperty
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Property<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionProperty(object|string $object_or_class, string $property)
		: Reflection_Property
	{
		// TODO: Implement newReflectionProperty() method.
		throw new ReflectionException('TODO: Implement newReflectionProperty() method.');
	}

	//-------------------------------------------------------------------------------------------- of
	public static function of(object|string $object_or_class) : static
	{
		return new static($object_or_class);
	}

}
