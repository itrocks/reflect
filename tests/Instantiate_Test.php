<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Reflection_Parameter;
use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Data\C;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class Instantiate_Test extends TestCase
{

	//----------------------------------------------------------------------------- testNewReflection
	/** @throws ReflectionException */
	public function testNewReflection() : void
	{
		$property = Reflection_Class::newReflection(Types::class, '$without');
		$method   = Reflection_Class::newReflection(Types::class, 'classReturnType');
		self::assertInstanceOf(Reflection_Property::class, $property);
		self::assertInstanceOf(Reflection_Method::class, $method);
		self::assertEquals(
			[Types::class, 'without'], [$property->getDeclaringClassName(), $property->getName()]
		);
		self::assertEquals(
			[Types::class, 'classReturnType'], [$method->getDeclaringClassName(), $method->getName()]
		);
	}

	//------------------------------------------------------------------------ testNewReflectionClass
	/** @throws ReflectionException */
	public function testNewReflectionClass() : void
	{
		$class = Reflection_Property::newReflection(Types::class);
		self::assertInstanceOf(Reflection_Class::class, $class);
		self::assertEquals(Types::class, $class->getName());
	}

	//----------------------------------------------------------------------- testNewReflectionMethod
	/** @throws ReflectionException */
	public function testNewReflectionMethod() : void
	{
		$method = Reflection_Class::newReflectionMethod(Types::class, 'classReturnType');
		self::assertInstanceOf(Reflection_Method::class, $method);
		self::assertEquals(
			[Types::class, 'classReturnType'], [$method->getDeclaringClassName(), $method->getName()]
		);
	}

	//-------------------------------------------------------------------- testNewReflectionParameter
	/** @throws ReflectionException */
	public function testNewReflectionParameter() : void
	{
		$parameter = Reflection_Class::newReflectionParameter([C::class, 'withParameter'], 'parameter');
		self::assertInstanceOf(Reflection_Parameter::class, $parameter);
		self::assertEquals([C::class, 'withParameter', 'parameter'], [
			$parameter->getDeclaringClass()?->getName(),
			$parameter->getDeclaringFunction()->getName(),
			$parameter->getName()
		]);
	}

	//--------------------------------------------------------------------- testNewReflectionProperty
	/** @throws ReflectionException */
	public function testNewReflectionProperty() : void
	{
		$property = Reflection_Class::newReflectionProperty(Types::class, 'without');
		self::assertInstanceOf(Reflection_Property::class, $property);
		self::assertEquals(
			[Types::class, 'without'], [$property->getDeclaringClassName(), $property->getName()]
		);
	}
	
}
