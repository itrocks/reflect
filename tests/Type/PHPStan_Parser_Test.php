<?php
namespace ITRocks\Reflect\Tests\Type;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHPStan\Collection;
use ITRocks\Reflect\Type\PHPStan\Parser;
use PHPUnit\Framework\TestCase;

class PHPStan_Parser_Test // phpcs:ignore
	extends TestCase
{

	//---------------------------------------------------------------------------------------- $array
	/** @var array<self> */
	public array $array = [];

	//------------------------------------------------------------------- testParseSquareBracketArray
	public function testParseSquareBracketArray() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection exists */
		$parser = new Parser(new Reflection_Property($this, 'array'), self::class . '[]');
		$type = $parser->parse();
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(1, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Named::class, $element);
		self::assertEquals(self::class, $element->getName());
	}

}
