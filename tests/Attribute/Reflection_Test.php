<?php
namespace ITRocks\Reflect\Tests\Attribute;

use Attribute;
use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Tests\Attribute\Data\C;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\Inheritable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Repeatable_Class;
use ITRocks\Reflect\Tests\Attribute\Data\Simple_Class;
use PHPUnit\Framework\TestCase;

class Reflection_Test extends TestCase
{

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

	//------------------------------------------------------------------------------ testGetAttribute
	public function testGetAttribute() : void
	{
		$class     = new Reflection_Class(C::class);
		$attribute = $class->getAttribute(Simple_Class::class);
		self::assertInstanceOf(Reflection_Attribute::class, $attribute, 'defined');
		self::assertEquals(Simple_Class::class, $attribute->getName(), 'named');
		self::assertNull($class->getAttribute(Attribute::class), 'undefined');
	}

	//--------------------------------------------------------------------- testGetAttributeInstances
	public function testGetAttributeInstances() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$instances = (new Reflection_Class(C::class))->getAttributeInstances(Simple_Class::class);
		self::assertCount(1, $instances);
		self::assertInstanceOf(Simple_Class::class, $instances[0]);
		self::assertEquals(12, $instances[0]->number);
	}

	//-------------------------------------------------------------------- testIsAttributeInheritable
	public function testIsAttributeInheritable() : void
	{
		$class = new Reflection_Class(C::class);
		self::assertTrue($class->isAttributeInheritable(Inheritable_Class::class), 'inheritable');
		self::assertFalse($class->isAttributeInheritable(Simple_Class::class), 'simple');
	}

	//--------------------------------------------------------------------- testIsAttributeRepeatable
	public function testIsAttributeRepeatable() : void
	{
		$class = new Reflection_Class(C::class);
		self::assertTrue($class->isAttributeRepeatable(Repeatable_Class::class), 'repeatable');
		self::assertFalse($class->isAttributeRepeatable(Simple_Class::class), 'simple');
	}
	
}
