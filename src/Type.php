<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Interfaces\Reflection;
use ITRocks\Reflect\Type\Reflection_Intersection_Type;
use ITRocks\Reflect\Type\Reflection_Named_Type;
use ITRocks\Reflect\Type\Reflection_Type;
use ITRocks\Reflect\Type\Reflection_Undefined_Type;
use ITRocks\Reflect\Type\Reflection_Union_Type;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

abstract class Type
{

	//-------------------------------------------------------------------------------------------- of
	public static function of(?ReflectionType $type, Reflection $reflection) : Reflection_Type
	{
		if ($type instanceof ReflectionNamedType) {
			return new Reflection_Named_Type($type, $reflection);
		}
		if ($type instanceof ReflectionUnionType) {
			return new Reflection_Union_Type($type, $reflection);
		}
		if ($type instanceof ReflectionIntersectionType) {
			return new Reflection_Intersection_Type($type, $reflection);
		}
		return new Reflection_Undefined_Type($reflection);
	}

}
