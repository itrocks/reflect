<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Class;
use PHPUnit\Framework\TestCase;

class Reflection_Class_Test extends TestCase
{

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------- testGetAttributesRepeatable
	public function testGetAttributesRepeatable() : void
	{
		$class = new Reflection_Class(C::class);

		$attributes = $class->getAttributes(Repeatable_Class::class, Reflection_Class::T_ALL);
		$values     = [];
		foreach ($attributes as $attribute) {
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2'], $values, 'repeatable');

		$attributes = $class->getAttributes(Inheritable_Repeatable::class, Reflection_Class::T_ALL);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable::class, $attribute->getName());
			/** @noinspection PhpUnhandledExceptionInspection valid */
			/** @var Inheritable_Repeatable $instance */
			$instance = $attribute->newInstance();
			$values[] = $instance->class;
		}
		self::assertEquals(
			['C1', 'C2', 'CT', 'CI', 'CIB', 'CII', 'CIIB', 'P', 'PT', 'PTT', 'PI'], $values, 'inheritable'
		);
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$class      = new Reflection_Class(C::class);
		$attributes = $class->getAttributes(Inheritable_Repeatable::class, Reflection_Class::T_ALL);
		self::assertCount(11, $attributes);
		$namespace = $class->getNamespaceName();
		foreach ($attributes as $attribute) {
			/** @noinspection PhpUnhandledExceptionInspection valid */
			/** @var Inheritable_Repeatable $instance */
			$instance = $attribute->newInstance();
			$expected = $namespace . '\\' . rtrim($instance->interface_trait, '12');
			self::assertEquals(
				$expected,
				$attribute->getDeclaringClass(true)->getName(),
				$instance->interface_trait . ' interface/trait'
			);
			self::assertEquals(
				$expected,
				$attribute->getDeclaringClass()->getName(),
				$instance->class . ' class'
			);
		}
	}

	//----------------------------------------------------------------------------- testGetFinalClass
	public function testGetFinalClass() : void
	{
		$class      = new Reflection_Class(C::class);
		$attributes = $class->getAttributes(Inheritable_Repeatable::class, Reflection_Class::T_ALL);
		self::assertCount(11, $attributes);
		foreach ($attributes as $attribute) {
			self::assertEquals(C::class, $attribute->getFinalClass()?->getName());
		}
	}

}
