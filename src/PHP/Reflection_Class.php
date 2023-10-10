<?php
namespace ITRocks\Reflect\PHP;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Interface\Reflection_Attribute;
use ITRocks\Reflect\Interface\Reflection_Class_Constant;
use ITRocks\Reflect\Interface\Reflection_Method;
use ITRocks\Reflect\Interface\Reflection_Property;
use ReflectionException;

/**
 * @implements Interface\Reflection_Class<Class>
 * @template Class of object
 */
class Reflection_Class implements Interface\Reflection_Class
{
	use Instantiate;

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

	//------------------------------------------------------------------------------------ __toString
	/** @return class-string<Class> */
	public function __toString() : string
	{
		return $this->name;
	}

	//---------------------------------------------------------------------------------- getAttribute
	public function getAttribute(string $name) : ?Reflection_Attribute
	{
		// TODO: Implement getAttribute() method.
		return null;
	}

	//------------------------------------------------------------------------- getAttributeInstances
	public function getAttributeInstances(string $name = null, int $flags = self::T_LOCAL) : array
	{
		// TODO: Implement getAttributeInstances() method.
		return [];
	}

	//--------------------------------------------------------------------------------- getAttributes
	public function getAttributes(string $name = null, int $flags = self::T_LOCAL) : array
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
		return substr($this->name, 0, (int)strrpos($this->name, '\\'));
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

	//------------------------------------------------------------------------- getReflectionConstant
	public function getReflectionConstant(string $name) : Reflection_Class_Constant
	{
		// TODO: Implement getReflectionConstant() method.
		throw new ReflectionException('TODO: Implement getReflectionConstant() method.');
	}

	//------------------------------------------------------------------------ getReflectionConstants
	public function getReflectionConstants(?int $filter = Interface\Reflection_Class::T_INHERIT)
		: array
	{
		// TODO: Implement getReflectionConstants() method.
		return [];
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

	//-------------------------------------------------------------------------------------------- of
	public static function of(object|string $object_or_class) : static
	{
		return new static($object_or_class);
	}

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		return $this->name;
	}

}
