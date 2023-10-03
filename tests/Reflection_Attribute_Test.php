<?php
namespace ITRocks\Reflect\Tests;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\CII;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Has_Default_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Repeatable;
use ITRocks\Reflect\Tests\Attribute\Data\P;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Simple_Class;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Foo('value1', 2)]
class Reflection_Attribute_Test extends TestCase
{

	//---------------------------------------------------------------------------------------- newFoo
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array{ReflectionAttribute<Foo>,Reflection_Attribute<Reflection_Class<$this>,Foo>,Reflection_Class<$this>}
	 */
	protected function newFoo() : array
	{
		/** @var ReflectionAttribute<Foo> $native_attribute */
		$native_attribute = (new ReflectionClass($this))->getAttributes(Foo::class)[0];
		/** @noinspection PhpUnhandledExceptionInspection object */
		$reflection_class     = new Reflection_Class($this);
		$reflection_attribute = new Reflection_Attribute($native_attribute, $reflection_class);
		return [$native_attribute, $reflection_attribute, $reflection_class];
	}

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------------ testConstructAttribute
	public function testConstructAttribute() : void
	{
		$native = (new ReflectionClass(C::class))->getAttributes(Simple_Class::class)[0] ?? null;
		self::assertNotNull($native);
		$attribute = new Reflection_Attribute($native, new Reflection_Class(C::class));
		self::assertEquals(Simple_Class::class, $attribute->getName(), 'getName');
		self::assertEquals([12], $attribute->getArguments(), 'getArguments');
		self::assertEquals(Attribute::TARGET_CLASS, $attribute->getTarget());
		self::assertEquals(C::class, $attribute->getDeclaring()->getName());
		self::assertEquals(C::class, $attribute->getFinal()->getName());
		/** @noinspection PhpUnhandledExceptionInspection valid */
		self::assertInstanceOf(Simple_Class::class, $attribute->newInstance());
	}

	//------------------------------------------------------------------------- testConstructInstance
	public function testConstructInstance() : void
	{
		$instance  = new Simple_Class(52);
		$attribute = new Reflection_Attribute($instance, new Reflection_Class(C::class));
		self::assertEquals(Simple_Class::class, $attribute->getName(), 'getName');
		self::assertEquals([], $attribute->getArguments(), 'getArguments');
		self::assertEquals(Attribute::TARGET_CLASS, $attribute->getTarget());
		self::assertEquals(C::class, $attribute->getDeclaring()->getName());
		self::assertEquals(C::class, $attribute->getFinal()->getName());
		/** @noinspection PhpUnhandledExceptionInspection valid */
		self::assertInstanceOf(Simple_Class::class, $attribute->newInstance());
	}

	//----------------------------------------------------------------------------- testConstructName
	public function testConstructName() : void
	{
		$attribute = new Reflection_Attribute(Simple_Class::class, new Reflection_Class(C::class));
		(new ReflectionProperty(Reflection_Attribute::class, 'arguments'))->setValue($attribute, [72]);
		self::assertEquals(Simple_Class::class, $attribute->getName(), 'getName');
		self::assertEquals([72], $attribute->getArguments(), 'getArguments');
		self::assertEquals(Attribute::TARGET_CLASS, $attribute->getTarget());
		self::assertEquals(C::class, $attribute->getDeclaring()->getName());
		self::assertEquals(C::class, $attribute->getFinal()->getName());
		/** @noinspection PhpUnhandledExceptionInspection valid */
		self::assertInstanceOf(Simple_Class::class, $attribute->newInstance());
	}

	//------------------------------------------------------------------------------ testGetArguments
	public function testGetArguments() : void
	{
		[$native_attribute, $reflection_attribute] = $this->newFoo();
		self::assertEquals(
			$native_attribute->getArguments(), $reflection_attribute->getArguments(), 'getArguments'
		);
	}

	//----------------------------------------------------------------------------------- testGetName
	public function testGetName() : void
	{
		[$native_attribute, $reflection_attribute] = $this->newFoo();
		self::assertEquals($native_attribute->getName(), $reflection_attribute->getName());
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
		$class      = new ReflectionClass(Has_Default_Class::class);
		$attributes = $class->getAttributes(Has_Default::class);
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

	//----------------------------------------------------------------------------- testIsInheritable
	public function testIsInheritable() : void
	{
		$attribute = (new Reflection_Class(C::class))->getAttribute(Inheritable_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		self::assertTrue($attribute->isInheritable(), 'inheritable');

		$attribute = (new Reflection_Class(C::class))->getAttribute(Simple_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		self::assertFalse($attribute->isInheritable(), 'simple');
	}

	//------------------------------------------------------------------------------ testIsRepeatable
	public function testIsRepeatable() : void
	{
		$attribute = (new Reflection_Class(C::class))->getAttribute(Repeatable_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		self::assertTrue($attribute->isRepeatable(), 'repeatable');

		$attribute = (new Reflection_Class(C::class))->getAttribute(Simple_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		self::assertFalse($attribute->isRepeatable(), 'simple');
	}

	//-------------------------------------------------------------------------------- testIsRepeated
	public function testIsRepeated() : void
	{
		$alone   = (new Reflection_Class(CII::class))->getAttribute(Inheritable_Repeatable::class);
		$inherit = (new Reflection_Class(P::class))->getAttribute(Inheritable_Repeatable::class);
		$locally = (new Reflection_Class(C::class))->getAttribute(Inheritable_Repeatable::class);
		self::assertInstanceOf(Reflection_Attribute::class, $alone);
		self::assertInstanceOf(Reflection_Attribute::class, $inherit);
		self::assertInstanceOf(Reflection_Attribute::class, $locally);
		self::assertFalse($alone->isRepeated(), 'alone');
		self::assertTrue($inherit->isRepeated(), 'inherit');
		self::assertTrue($locally->isRepeated(), 'locally');
	}

	//------------------------------------------------------------------------------- testNewInstance
	public function testNewInstance() : void
	{
		$attribute = (new Reflection_Class(C::class))->getAttribute(Simple_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		/** @noinspection PhpUnhandledExceptionInspection class */
		$instance = $attribute->newInstance();
		self::assertInstanceOf(Simple_Class::class, $instance);
		self::assertEquals(12, $instance->number);
	}

	//------------------------------------------------------------------------------ testRepeatableOf
	public function testRepeatableOf() : void
	{
		$attributes = Repeatable_Class::of(new Reflection_Class(C::class));
		self::assertNotCount(0, $attributes);
		foreach ($attributes as $attribute) {
			self::assertInstanceOf(Repeatable_Class::class, $attribute);
		}
	}

	//---------------------------------------------------------------------------------- testSingleOf
	public function testSingleOf() : void
	{
		$attribute = Simple_Class::of(new Reflection_Class(C::class));
		self::assertNotNull($attribute);
		self::assertInstanceOf(Simple_Class::class, $attribute);
	}

	//------------------------------------------------------------------------------- testWithNoClass
	public function testWithNoClass() : void
	{
		/** @phpstan-ignore-next-line named */
		$attribute = (new Reflection_Class(C::class))->getAttribute('with_no_class');
		self::assertNotNull($attribute);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute);
		self::assertEquals('with_no_class', $attribute->getName());
		self::assertEquals([0 => 5], $attribute->getArguments());
		$this->expectException(ReflectionException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage('Attribute class "with_no_class" not found');
		$attribute->newInstance();
	}

}
