<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Class;
use PHPUnit\Framework\TestCase;

class Reflection_Class_Test extends TestCase
{
	use Commons;

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

		$attributes = $class->getAttributes(
			Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL
		);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable_Class::class, $attribute->getName());
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(
			['C1', 'C2', 'CT', 'CI', 'CI2', 'CII', 'CII2', 'P', 'PT', 'PTT', 'PI'], $values, 'inheritable'
		);
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$class      = new Reflection_Class(C::class);
		$attributes = $class->getAttributes(
			Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL
		);
		self::assertCount(11, $attributes);
		$namespace = $class->getNamespaceName();
		$this->getDeclaringClassCommons($attributes, $namespace);
	}

	//----------------------------------------------------------------------------- testGetFinalClass
	public function testGetFinalClass() : void
	{
		$class      = new Reflection_Class(C::class);
		$attributes = $class->getAttributes(
			Inheritable_Repeatable_Class::class, Reflection_Class::T_ALL
		);
		self::assertCount(11, $attributes);
		foreach ($attributes as $attribute) {
			self::assertEquals(C::class, $attribute->getFinalClass()?->getName());
		}
	}

}
