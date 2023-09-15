<?php
namespace ITRocks\Reflect\Interfaces;

/**
 * Common interface for reflection objects
 */
interface Reflection
{

	//------------------------------------------------------------------------- DOC_COMMENT_AGGREGATE
	public const DOC_COMMENT_AGGREGATE = "\t *IN ";

	//-------------------------------------------------------------------------------- $filter values
	// 1=IS_PUBLIC, 2=IS_PROTECTED, 4=IS_PRIVATE, 8=?, 16=IS_STATIC, 32=IS_FINAL, 64=IS_ABSTRACT
	public const T_EXTENDS    = 1024;
	public const T_IMPLEMENTS = 2048;
	public const T_INHERIT    = self::T_EXTENDS | self::T_IMPLEMENTS | self::T_USE;
	public const T_LOCAL      = 0;
	public const T_USE        = 4096;

	//--------------------------------------------------------------------------------- getDocComment
	/** @param int<0,max> $filter self::T_EXTEND|self::T_IMPLEMENT|self::T_USE */
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false;

	//--------------------------------------------------------------------------------------- getName
	public function getName() : ?string;

	//--------------------------------------------------------------------------------- newReflection
	/** @param class-string $class */
	public static function newReflection(string $class, string $member = '') : Reflection;

	//---------------------------------------------------------------------------- newReflectionClass
	/**
	 * @param class-string<T> $class
	 * @return Reflection_Class<T>
	 * @template T of object
	 */
	public static function newReflectionClass(string $class) : Reflection_Class;

	//--------------------------------------------------------------------------- newReflectionMethod
	/** @param class-string $class */
	public static function newReflectionMethod(string $class, string $method) : Reflection_Method;

	//------------------------------------------------------------------------ newReflectionParameter
	/** @param array{object|string,string}|object|string $function */
	public static function newReflectionParameter(array|object|string $function, int|string $param)
		: Reflection_Parameter;

	//------------------------------------------------------------------------- newReflectionProperty
	/** @param class-string $class */
	public static function newReflectionProperty(string $class, string $property)
		: Reflection_Property;

}
