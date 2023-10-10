<?php
namespace ITRocks\Reflect\PHP;

use ITRocks\Reflect\Interface\Reflection_Class_Constant;
use ITRocks\Reflect\Interface\Reflection_Method;
use ITRocks\Reflect\Interface\Reflection_Parameter;
use ITRocks\Reflect\Interface\Reflection_Property;
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
		// TODO: Implement newReflection() method.
		throw new ReflectionException('TODO: Implement newReflection() method.');
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
		// TODO: Implement newReflectionClass() method.
		throw new ReflectionException('TODO: Implement newReflectionClass() method.');
	}

	//------------------------------------------------------------------------- newReflectionConstant
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared C of object
	 * @param class-string<C>|C $object_or_class
	 * @return Reflection_Class_Constant<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionConstant(object|string $object_or_class, string $constant)
		: Reflection_Class_Constant
	{
		// TODO: Implement newReflectionClass() method.
		throw new ReflectionException('TODO: Implement newReflectionClassConstant() method.');
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
		// TODO: Implement newReflectionMethod() method.
		throw new ReflectionException('TODO: Implement newReflectionMethod() method.');
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
	public static function newReflectionParameter(object|array|string $function, int|string $param)
		: Reflection_Parameter
	{
		// TODO: Implement newReflectionParameter() method.
		throw new ReflectionException('TODO: Implement newReflectionParameter() method.');
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
		// TODO: Implement newReflectionProperty() method.
		throw new ReflectionException('TODO: Implement newReflectionProperty() method.');
	}

}
