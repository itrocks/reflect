<?php
namespace ITRocks\Reflect\Tests;

use Attribute;
use ITRocks\Reflect\Attribute\Inheritable;
use ITRocks\Reflect\Attribute\Override;
use ITRocks\Reflect\Reflection_Attribute_Override;
use ITRocks\Reflect\Reflection_Class;
use PHPUnit\Framework\TestCase;

#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
#[Override('property', Reflection_Attribute_Override_Test::class)]
class Reflection_Attribute_Override_Test extends TestCase
{

	//------------------------------------------------------------------------------------- $property
	public int $property = 0;

	//------------------------------------------------------------------------------- testGetOverride
	public function testGetOverride() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection exists */
		$class     = new Reflection_Class($this);
		$override  = $class->getAttribute(Override::class);
		$property  = $class->getProperty('property');
		$attribute = $property->getAttribute(Reflection_Attribute_Override_Test::class);
		self::assertInstanceOf(Reflection_Attribute_Override::class, $attribute);
		self::assertEquals(print_r($override, true), print_r($attribute->getOverride(), true));
	}

}
