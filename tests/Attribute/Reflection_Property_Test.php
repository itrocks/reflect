<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Attribute\Data\All_Targets;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Property;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Property_Child;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable;
use ITRocks\Reflect\Tests\Attribute\Data\P;
use ITRocks\Reflect\Tests\Attribute\Data\PI;
use ITRocks\Reflect\Tests\Attribute\Data\PT;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionMethod;

class Reflection_Property_Test extends TestCase
{

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------ testGetAttributesInheritable
	public function testGetAttributesInheritable() : void
	{
		$property   = new Reflection_Property(C::class, 'inheritable');
		$attributes = $property->getAttributes(All_Targets::class, Reflection_Property::T_ALL);
		self::assertCount(0, $attributes, 'not inheritable');
		$attributes = $property->getAttributes(Inheritable_Property::class, Reflection_Property::T_ALL);
		self::assertCount(1, $attributes);
		self::assertEquals(
			Inheritable_Property::class, $attributes[0]->getName(), 'inheritable not repeatable'
		);
	}

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
			Inheritable_Repeatable::class, Reflection_Property::T_ALL
		);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable::class, $attribute->getName());
			/** @var Inheritable_Repeatable $instance */
			/** @noinspection PhpUnhandledExceptionInspection class */
			$instance = $attribute->newInstance();
			$values[] = $instance->interface_trait;
		}
		self::assertEquals(['OC1', 'OC2', 'C1', 'C2', 'OCT', 'CT', 'OCI', 'OCIB', 'OCII', 'OCIIB', 'OP', 'P', 'OPT', 'PT', 'OPTT', 'PTT', 'OPI'], $values, 'inheritable repeatable');
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$property   = new Reflection_Property(C::class, 'inheritable_repeatable');
		$attributes = $property->getAttributes(
			Inheritable_Repeatable::class, Reflection_Property::T_ALL
		);
		self::assertCount(17, $attributes);
		$namespace = $property->getFinalClass()->getNamespaceName();
		foreach ($attributes as $attribute) {
			/** @noinspection PhpUnhandledExceptionInspection valid */
			/** @var Inheritable_Repeatable $instance */
			$instance = $attribute->newInstance();
			$expected = $namespace . '\\' . rtrim(ltrim($instance->interface_trait, 'O'), '12');
			self::assertEquals(
				$expected,
				$attribute->getDeclaringClass(true)->getName(),
				$instance->interface_trait . ' interface/trait'
			);
			$expected = $namespace . '\\' . rtrim(ltrim($instance->class, 'O'), 'I12');
			self::assertEquals(
				$expected, $attribute->getDeclaringClass()->getName(), $instance->interface_trait . ' class'
			);
		}
	}

	//----------------------------------------------------------------------------- testGetFinalClass
	public function testGetFinalClass() : void
	{
		$property   = new Reflection_Property(C::class, 'inheritable_repeatable');
		$attributes = $property->getAttributes(
			Inheritable_Repeatable::class, Reflection_Property::T_ALL
		);
		self::assertCount(17, $attributes);
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
		$method = new ReflectionMethod(Reflection_Property::class, 'hasSameAttributes');
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$property1 = new Reflection_Property($class, $property);
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$property2 = new Reflection_Property($than, $property);
		/** @noinspection PhpUnhandledExceptionInspection valid */
		self::assertEquals($expected, $method->invoke($property1, $property2), "data set #$key");
	}

	//---------------------------------------------------------------------- testInheritableWithBreak
	public function testInheritableWithBreak() : void
	{
		$actual   = [];
		$property = new Reflection_Property(C::class, 'inheritable_with_break');
		foreach ($property->getAttributes(null, Reflection_Property::T_ALL) as $attribute) {
			/** @noinspection PhpUnhandledExceptionInspection all attributes must be valid classes */
			/** @var Inheritable_Repeatable $instance */
			$instance = $attribute->newInstance();
			$actual[] = [
				$instance->class,
				$instance->interface_trait,
				$attribute->getDeclaringClass()->name,
				$attribute->getDeclaringClass(true)->name
			];
		}
		$expected = [
			['C',  'C',   C::class, C::class],
			['OP', 'OP',  P::class, P::class],
			['OP', 'OPT', P::class, PT::class],
			['P',  'PT',  P::class, PT::class],
			['OP', 'OPI', P::class, PI::class]
		];
		self::assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------------ testOverrideInstanceOf
	public function testOverrideInstanceOf() : void
	{
		$property = new Reflection_Property(C::class, 'override_instance_of');

		$actual     = [];
		$attributes = $property->getAttributes(Inheritable_Property::class, Reflection_Property::T_ALL);
		foreach ($attributes as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Property::class];
		self::assertEquals($expected, $actual, 'exact name');

		$actual     = [];
		$attributes = $property->getAttributes(
			Inheritable_Property::class, ReflectionAttribute::IS_INSTANCEOF | Reflection_Property::T_ALL
		);
		foreach ($attributes as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Property_Child::class];
		self::assertEquals($expected, $actual, 'instance-of');
	}

	//------------------------------------------------------------------------------ testOverrideName
	public function testOverrideName() : void
	{
		$actual   = [];
		$property = new Reflection_Property(C::class, 'override_name');
		foreach ($property->getAttributes(null, Reflection_Property::T_OVERRIDE) as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Property::class];
		self::assertEquals($expected, $actual);
	}

}
