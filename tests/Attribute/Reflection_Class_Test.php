<?php
namespace ITRocks\Reflect\Tests\Attribute;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Has_Default_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Class;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class Reflection_Class_Test extends TestCase
{

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$class      = new Reflection_Class(C::class);
		$attributes = $class->getAttributes(
			Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL
		);
		$namespace = $class->getNamespaceName();
		foreach ($attributes as $attribute) {
			$argument = $attribute->getArguments()[0];
			$awaited  = $namespace . '\\' . $argument;
			if (str_ends_with($awaited, '\\C1') || str_ends_with($awaited, '\\C2')) {
				$awaited = substr($awaited, 0, -1);
			}
			self::assertEquals(
				$awaited, $attribute->getDeclaringClass(true)->getName(), $argument . ' interface/trait'
			);
			$awaited = rtrim(str_replace(['1', '2'], '', $awaited), 'IT');
			self::assertEquals(
				$awaited, $attribute->getDeclaringClass()->getName(), $argument . ' class'
			);
		}
	}

	//----------------------------------------------------------------------------- testGetFinalClass
	public function testGetFinalClass() : void
	{
		$attributes = (new Reflection_Class(C::class))->getAttributes(
			Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL
		);
		foreach ($attributes as $attribute) {
			self::assertEquals(C::class, $attribute->getFinalClass()?->getName());
		}
	}

	//--------------------------------------------------------------------------------- testGetTarget
	public function testGetTarget() : void
	{
		foreach ((new Reflection_Class(Foo::class))->getAttributes() as $attribute) {
			self::assertEquals(Attribute::TARGET_CLASS, $attribute->getTarget());
		}
		$attributes = (new Reflection_Class(C::class))->getAttributes(null, Reflection_Class::T_ALL);
		foreach ($attributes as $attribute) {
			self::assertEquals(Attribute::TARGET_CLASS, $attribute->getTarget());
		}
	}

	//-------------------------------------------------------------------------------- testHasDefault
	public function testHasDefault() : void
	{
		$attributes = (new ReflectionClass(Has_Default_Class::class))->getAttributes(Has_Default::class);
		self::assertCount(1, $attributes);
		/** @var Has_Default $instance */
		$instance = $attributes[0]->newInstance();
		self::assertEquals(['default'], $instance->arguments);

		$attributes = (new Reflection_Class(C::class))->getAttributes(Has_Default_Class::class);
		self::assertCount(1, $attributes);
		self::assertInstanceOf(Reflection_Attribute::class, $attributes[0]);
		self::assertEquals(Has_Default_Class::class, $attributes[0]->getName());
		self::assertCount(1, $attributes[0]->getArguments());
		self::assertEquals(['default'], $attributes[0]->getArguments());
	}

	//----------------------------------------------------------------- testRepeatableClassAttributes
	public function testRepeatableClassAttributes() : void
	{
		$class = new Reflection_Class(C::class);

		$attributes = $class->getAttributes(Repeatable_Class::class);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Repeatable_Class::class, $attribute->getName());
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2'], $values);

		$attributes = $class->getAttributes(Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable_Class::class, $attribute->getName());
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2', 'CI', 'CI2', 'CII', 'CII2', 'P', 'PT', 'PTT', 'PI'], $values);
	}

}
