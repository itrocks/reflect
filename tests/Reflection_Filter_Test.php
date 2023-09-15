<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interfaces\Reflection_Property;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Reflection_Parameter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class Reflection_Filter_Test extends TestCase
{

	//------------------------------------------------------------------ testReflectionClassConstants
	public function testReflectionClassConstants() : void
	{
		$bits = 0;
		$constants = (new ReflectionClass(Reflection_Class::class))->getConstants();
		foreach ($constants as $name => $bit) {
			if (($name === 'T_INHERIT') || !is_int($bit)) {
				continue;
			}
			self::assertEquals(0, $bits & $bit, $name);
			$bits |= $bit;
		}
	}

	//----------------------------------------------------------------- testReflectionMethodConstants
	public function testReflectionMethodConstants() : void
	{
		$bits = 0;
		$constants = (new ReflectionClass(Reflection_Method::class))->getConstants();
		foreach ($constants as $name => $bit) {
			if (($name === 'T_INHERIT') || !is_int($bit)) {
				continue;
			}
			self::assertEquals(0, $bits & $bit, $name);
			$bits |= $bit;
		}
	}

	//------------------------------------------------------------- testReflectionParametersConstants
	public function testReflectionParametersConstants() : void
	{
		$bits = 0;
		$constants = (new ReflectionClass(Reflection_Parameter::class))->getConstants();
		foreach ($constants as $name => $bit) {
			if (($name === 'T_INHERIT') || !is_int($bit)) {
				continue;
			}
			self::assertEquals(0, $bits & $bit, $name);
			$bits |= $bit;
		}
	}

	//--------------------------------------------------------------- testReflectionPropertyConstants
	public function testReflectionPropertyConstants() : void
	{
		$bits = 0;
		$constants = (new ReflectionClass(Reflection_Property::class))->getConstants();
		foreach ($constants as $name => $bit) {
			if (($name === 'T_INHERIT') || !is_int($bit)) {
				continue;
			}
			self::assertEquals(0, $bits & $bit, $name);
			$bits |= $bit;
		}
	}

}
