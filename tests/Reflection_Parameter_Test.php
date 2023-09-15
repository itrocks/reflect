<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Reflection_Parameter;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Type\Reflection_Named_Type;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class Reflection_Parameter_Test extends TestCase
{

	//----------------------------------------------------------------------------- testGetDocComment
	public function testGetDocComment() : void
	{
		$this->expectException(ReflectionException::class);
		$this->expectExceptionMessage('This feature has not been implemented yet');
		(new Reflection_Parameter([C::class, 'withParameter'], 'parameter'))->getDocComment();
	}

	//----------------------------------------------------------------------------------- testGetType
	public function testGetType() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid parameter */
		$type = (new Reflection_Parameter([C::class, 'withParameter'], 'parameter'))->getType();
		self::assertInstanceOf(Reflection_Named_Type::class, $type);
		self::assertEquals('string', $type->getName());
	}

	//---------------------------------------------------------------------------------- testToString
	public function testToString() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid parameter */
		$string = strval(new Reflection_Parameter([C::class, 'withParameter'], 'parameter'));
		self::assertEquals("string &\$parameter = 'default'", $string);
	}

}
