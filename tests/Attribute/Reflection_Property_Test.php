<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable_Property;
use ITRocks\Reflect\Tests\Attribute\Data\P;
use ITRocks\Reflect\Tests\Attribute\Data\PT;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class Reflection_Property_Test extends TestCase
{
	use Commons;

	//------------------------------------------------------------------- testGetAttributesRepeatable
	public function testGetAttributesRepeatable() : void
	{
		$property = new Reflection_Property(C::class, 'inheritable_repeatable');

		$attributes = $property->getAttributes(Repeatable_Property::class, Reflection_Property::T_ALL);
		$values     = [];
		foreach ($attributes as $attribute) {
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2'], $values, 'repeatable');

		$attributes = $property->getAttributes(
			Inheritable_Repeatable_Property::class, Reflection_Property::T_INHERIT
		);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable_Property::class, $attribute->getName());
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2', 'CT', 'P', 'PT', 'PTT'], $values, 'inheritable');
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$property   = new Reflection_Property(C::class, 'inheritable_repeatable');
		$attributes = $property->getAttributes(
			Inheritable_Repeatable_Property::class, Reflection_Property::T_ALL
		);
		self::assertCount(6, $attributes);
		$namespace = $property->getFinalClass()->getNamespaceName();
		$this->getDeclaringClassCommons($attributes, $namespace);
	}

	//----------------------------------------------------------------------------- testGetFinalClass
	public function testGetFinalClass() : void
	{
		$property   = new Reflection_Property(C::class, 'inheritable_repeatable');
		$attributes = $property->getAttributes(
			Inheritable_Repeatable_Property::class, Reflection_Property::T_ALL
		);
		self::assertCount(6, $attributes);
		foreach ($attributes as $attribute) {
			self::assertEquals(C::class, $attribute->getFinalClass()?->getName());
		}
	}

	//------------------------------------------------------------------------- testHasSameAttributes
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param int<0,max> $key
	 * @param class-string $class
	 * @param class-string $than
	 */
	#[TestWith([0, P::class, PT::class, 'not_same_attribute_count', false])]
	#[TestWith([1, P::class, PT::class, 'not_same_attribute_name', false])]
	#[TestWith([2, P::class, PT::class, 'same_attributes', true])]
	public function testHasSameAttributes(
		int $key, string $class, string $than, string $property, bool $expected
	) : void
	{
		class_exists(Foo::class);
		$method    = new ReflectionMethod(Reflection_Property::class, 'hasSameAttributes');
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$property1 = new Reflection_Property($class, $property);
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$property2 = new Reflection_Property($than, $property);
		/** @noinspection PhpUnhandledExceptionInspection valid */
		self::assertEquals($expected, $method->invoke($property1, $property2), "data set #$key");
	}

}
