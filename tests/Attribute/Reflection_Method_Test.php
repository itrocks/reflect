<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Tests\Attribute\Data\All_Targets;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Method;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Method_Child;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable;
use ITRocks\Reflect\Tests\Attribute\Data\P;
use ITRocks\Reflect\Tests\Attribute\Data\PI;
use ITRocks\Reflect\Tests\Attribute\Data\PT;
use ITRocks\Reflect\Tests\Attribute\Data\PTT;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Method;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;

class Reflection_Method_Test extends TestCase
{

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------ testGetAttributesInheritable
	public function testGetAttributesInheritable() : void
	{
		$method     = new Reflection_Method(C::class, 'inheritable');
		$attributes = $method->getAttributes(All_Targets::class, Reflection_Method::T_ALL);
		self::assertCount(0, $attributes, 'not inheritable');
		$attributes = $method->getAttributes(Inheritable_Method::class, Reflection_Method::T_ALL);
		self::assertCount(1, $attributes);
		self::assertEquals(
			Inheritable_Method::class, $attributes[0]->getName(), 'inheritable not repeatable'
		);
	}

	//------------------------------------------------------------------- testGetAttributesRepeatable
	public function testGetAttributesRepeatable() : void
	{
		$method     = new Reflection_Method(C::class, 'inheritableRepeatable');
		$attributes = $method->getAttributes(Repeatable_Method::class, Reflection_Method::T_ALL);
		$values     = [];
		foreach ($attributes as $attribute) {
			$values[] = $attribute->getArguments()[0];
		}
		self::assertEquals(['C1', 'C2'], $values, 'repeatable');

		$attributes = $method->getAttributes(
			Inheritable_Repeatable::class, Reflection_Method::T_ALL
		);
		$values = [];
		foreach ($attributes as $attribute) {
			self::assertEquals(Inheritable_Repeatable::class, $attribute->getName());
			/** @var Inheritable_Repeatable $instance */
			/** @noinspection PhpUnhandledExceptionInspection class */
			$instance = $attribute->newInstance();
			$values[] = $instance->interface_trait;
		}
		self::assertEquals(
			[
				'OC1', 'OC2', 'C1', 'C2', 'OCT', 'CT', 'OCI', 'OCIB', 'OCII', 'OCIIB',
				'OP', 'P', 'OPT', 'PT', 'OPTT', 'PTT', 'OPI'
			],
			$values,
			'inheritable repeatable'
		);
	}

	//------------------------------------------------------------------------- testGetDeclaringClass
	public function testGetDeclaringClass() : void
	{
		$method     = new Reflection_Method(C::class, 'inheritableRepeatable');
		$attributes = $method->getAttributes(
			Inheritable_Repeatable::class, Reflection_Method::T_ALL
		);
		self::assertCount(17, $attributes);
		$namespace = $method->getFinalClass()->getNamespaceName();
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
		$method   = new Reflection_Method(C::class, 'inheritableRepeatable');
		$attributes = $method->getAttributes(
			Inheritable_Repeatable::class, Reflection_Method::T_ALL
		);
		self::assertCount(17, $attributes);
		foreach ($attributes as $attribute) {
			self::assertEquals(C::class, $attribute->getFinalClass()?->getName());
		}
	}

	//---------------------------------------------------------------------- testInheritableWithBreak
	public function testInheritableWithBreak() : void
	{
		$actual = [];
		$method = new Reflection_Method(C::class, 'inheritableWithBreak');
		foreach ($method->getAttributes(null, Reflection_Method::T_ALL) as $attribute) {
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
			['C',  'C',    C::class, C::class],
			['OP', 'OP',   P::class, P::class],
			['OP', 'OPT',  P::class, PT::class],
			['P',  'PT',   P::class, PT::class],
			['OP', 'OPTT', P::class, PTT::class],
			['OP', 'OPI',  P::class, PI::class]
		];
		self::assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------------ testOverrideInstanceOf
	public function testOverrideInstanceOf() : void
	{
		$method = new Reflection_Method(C::class, 'overrideInstanceOf');

		$actual     = [];
		$attributes = $method->getAttributes(Inheritable_Method::class, Reflection_Method::T_ALL);
		foreach ($attributes as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Method::class];
		self::assertEquals($expected, $actual, 'exact name');

		$actual     = [];
		$attributes = $method->getAttributes(
			Inheritable_Method::class, ReflectionAttribute::IS_INSTANCEOF | Reflection_Method::T_ALL
		);
		foreach ($attributes as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Method_Child::class];
		self::assertEquals($expected, $actual, 'instance-of');
	}

	//------------------------------------------------------------------------------ testOverrideName
	public function testOverrideName() : void
	{
		$actual = [];
		$method = new Reflection_Method(C::class, 'overrideName');
		foreach ($method->getAttributes(null, Reflection_Method::T_OVERRIDE) as $attribute) {
			$actual[] = $attribute->getName();
		}
		$expected = [Inheritable_Method::class];
		self::assertEquals($expected, $actual);
	}

}
