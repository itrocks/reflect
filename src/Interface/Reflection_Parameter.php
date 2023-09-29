<?php
namespace ITRocks\Reflect\Interface;

use ReflectionException;
use Stringable;

/** @template Class of object */
interface Reflection_Parameter extends Reflection, Stringable
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param array{class-string<Class>|Class,string} $method
	 * @throws ReflectionException
	 */
	public function __construct(array $method, int|string $param);

	//-------------------------------------------------------------------------- getDeclaringFunction
	/** @return Reflection_Method<Class> */
	public function getDeclaringFunction() : Reflection_Method;

}
