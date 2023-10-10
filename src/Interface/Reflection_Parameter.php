<?php
namespace ITRocks\Reflect\Interface;

use ReflectionException;

/** @template Class of object */
interface Reflection_Parameter extends Reflection
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Argument type does not match the declared Class is object
	 * @param array{class-string<Class>|Class,string}|Class|string $function
	 * @param non-negative-int|string $param
	 * @throws ReflectionException
	 */
	public function __construct(array|object|string $function, int|string $param);

	//-------------------------------------------------------------------------- getDeclaringFunction
	/** @return Reflection_Method<Class> */
	public function getDeclaringFunction() : Reflection_Method;

}
