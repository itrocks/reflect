<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Interfaces\Reflection;
use ReflectionException;

trait Instantiates
{

	//--------------------------------------------------------------------------------- newReflection
	/**
	 * @param class-string $class
	 * @throws ReflectionException
	 */
	public static function newReflection(string $class, string $member = '') : Reflection
	{
		if ($member === '') {
			return new Reflection_Class($class);
		}
		return str_starts_with($member, '$')
			? new Reflection_Property($class, $member)
			: new Reflection_Method($class, $member);
	}

	//---------------------------------------------------------------------------- newReflectionClass
	/**
	 * @param class-string<C> $class
	 * @return Reflection_Class<C>
	 * @template C of object
	 * @throws ReflectionException
	 */
	public static function newReflectionClass(string $class) : Reflection_Class
	{
		return new Reflection_Class($class);
	}

	//--------------------------------------------------------------------------- newReflectionMethod
	/**
	 * @param class-string $class
	 * @throws ReflectionException
	 */
	public static function newReflectionMethod(string $class, string $method) : Reflection_Method
	{
		return new Reflection_Method($class, $method);
	}

	//------------------------------------------------------------------------ newReflectionParameter
	/**
	 * @param array{object|string,string}|object|string $function
	 * @throws ReflectionException
	 */
	public static function newReflectionParameter(array|object|string $function, int|string $param)
		: Reflection_Parameter
	{
		return new Reflection_Parameter($function, $param);
	}

	//------------------------------------------------------------------------- newReflectionProperty
	/**
	 * @param class-string $class
	 * @throws ReflectionException
	 */
	public static function newReflectionProperty(string $class, string $property)
		: Reflection_Property
	{
		return new Reflection_Property($class, $property);
	}

}
