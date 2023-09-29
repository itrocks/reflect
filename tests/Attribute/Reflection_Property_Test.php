<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;
use ITRocks\Reflect\Tests\Attribute\Data\P;
use ITRocks\Reflect\Tests\Attribute\Data\PT;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class Reflection_Property_Test extends TestCase
{

	//------------------------------------------------------------------------- testHasSameAttributes
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param int<0,max> $key
	 * @param class-string $class
	 * @param class-string $than
	 */
	#[TestWith([0, P::class, PT::class, 'not_same_attribute_count', false])]
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
