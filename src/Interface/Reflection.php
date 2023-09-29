<?php
namespace ITRocks\Reflect\Interface;

use ReflectionAttribute;
use ReflectionException;

interface Reflection
{

	//------------------------------------------------------------------------- DOC_COMMENT_AGGREGATE
	public const DOC_COMMENT_AGGREGATE = "\t *IN ";

	//-------------------------------------------------------------------------------- $filter values
	public const T_ALL        = self::T_INHERIT;
	public const T_EXTENDS    = 1024;
	public const T_IMPLEMENTS = 2048;
	public const T_INHERIT    = self::T_EXTENDS | self::T_IMPLEMENTS | self::T_USE;
	public const T_LOCAL      = 0;
	public const T_USE        = 4096;

	//---------------------------------------------------------------------------------- getAttribute
	/**
	 * @param class-string<A> $name
	 * @return ?Reflection_Attribute<$this,A>
	 * @template A of object
	 */
	public function getAttribute(string $name) : ?Reflection_Attribute;

	//------------------------------------------------------------------------- getAttributeInstances
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @return list<A>
	 * @template A of object
	 * @throws ReflectionException
	 */
	public function getAttributeInstances(string $name = null, int $flags = 0) : array;

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	public function getAttributes(string $name = null, int $flags = 0) : array;

	//--------------------------------------------------------------------------------- getDocComment
	/** @param int-mask-of<self::T_*> $filter */
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false;

	//--------------------------------------------------------------------------------------- getName
	public function getName() : ?string;

	//--------------------------------------------------------------------------------- newReflection
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return ($member is null ? Reflection_Class<C> : Reflection_Method<C>|Reflection_Property<C>)
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflection(object|string $object_or_class, string $member = null)
		: Reflection_Class|Reflection_Method|Reflection_Property;

	//---------------------------------------------------------------------------- newReflectionClass
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Class<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionClass(object|string $object_or_class) : Reflection_Class;

	//--------------------------------------------------------------------------- newReflectionMethod
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Method<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionMethod(object|string $object_or_class, string $method)
		: Reflection_Method;

	//------------------------------------------------------------------------ newReflectionParameter
	/**
	 * @param array{class-string<C>|C,string} $function
	 * @return Reflection_Parameter<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionParameter(array $function, int|string $param)
		: Reflection_Parameter;

	//------------------------------------------------------------------------- newReflectionProperty
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Property<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionProperty(object|string $object_or_class, string $property)
		: Reflection_Property;

	//------------------------------------------------------------------------------------------ path
	public function path() : string;

}
