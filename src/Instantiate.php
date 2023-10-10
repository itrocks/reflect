<?php
namespace ITRocks\Reflect;

use ReflectionException;

trait Instantiate
{

	//--------------------------------------------------------------------------------- newReflection
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return ($member is null ? Reflection_Class<C> : Reflection_Method<C>|Reflection_Property<C>)
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflection(object|string $object_or_class, string $member = null)
		: Reflection_Class|Reflection_Method|Reflection_Property
	{
		if (is_null($member)) {
			return new Reflection_Class($object_or_class);
		}
		return str_starts_with($member, '$')
			? new Reflection_Property($object_or_class, substr($member, 1))
			: new Reflection_Method($object_or_class, $member);
	}

	//---------------------------------------------------------------------------- newReflectionClass
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Class<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionClass(object|string $object_or_class) : Reflection_Class
	{
		return new Reflection_Class($object_or_class);
	}

	//------------------------------------------------------------------------- newReflectionConstant
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Class_Constant<C>
	 * @template C of object
	 */
	public static function newReflectionConstant(object|string $object_or_class, string $constant)
		: Reflection_Class_Constant
	{
		return new Reflection_Class_Constant($object_or_class, $constant);
	}

	//--------------------------------------------------------------------------- newReflectionMethod
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Method<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionMethod(object|string $object_or_class, string $method)
		: Reflection_Method
	{
		return new Reflection_Method($object_or_class, $method);
	}

	//------------------------------------------------------------------------ newReflectionParameter
	/**
	 * @noinspection PhpDocSignatureInspection $function Argument type does not match the declared C of object
	 * @param array{class-string<C>|C,string}|C|string $function
	 * @param non-negative-int|string                  $param
	 * @return Reflection_Parameter<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionParameter(array|object|string $function, int|string $param)
		: Reflection_Parameter
	{
		return new Reflection_Parameter($function, $param);
	}

	//------------------------------------------------------------------------- newReflectionProperty
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Property<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionProperty(object|string $object_or_class, string $property)
		: Reflection_Property
	{
		return new Reflection_Property($object_or_class, $property);
	}

}
