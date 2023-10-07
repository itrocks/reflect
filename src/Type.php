<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Native\Intersection;
use ITRocks\Reflect\Type\Native\Named;
use ITRocks\Reflect\Type\Native\Union;
use ITRocks\Reflect\Type\Undefined;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

abstract class Type
{

	//----------------------------------------------------------------------------------------- ARRAY
	const ARRAY    = 'array';
	const BOOL     = 'bool';
	const CALLABLE = 'callable';
	const FALSE    = 'false';
	const FLOAT    = 'float';
	const INT      = 'int';
	const ITERABLE = 'iterable';
	const MIXED    = 'mixed';
	const NEVER    = 'never';
	const NULL     = 'null';
	const OBJECT   = 'object';
	const STRING   = 'string';
	const TRUE     = 'true';
	const VOID     = 'void';

	//-------------------------------------------------------------------------------------------- of
	public static function of(?ReflectionType $type, Reflection $reflection) : Reflection_Type
	{
		if ($type instanceof ReflectionNamedType) {
			return new Named($type, $reflection);
		}
		if ($type instanceof ReflectionUnionType) {
			return new Union($type, $reflection);
		}
		if ($type instanceof ReflectionIntersectionType) {
			return new Intersection($type, $reflection);
		}
		return new Undefined($reflection);
	}

}
