<?php
namespace ITRocks\Reflect\Tests\Type;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHPStan\Collection;
use ITRocks\Reflect\Type\PHPStan\Parser;
use ITRocks\Reflect\Type\Undefined;
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
		$type   = $parser->parse();
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(1, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Named::class, $element);
		self::assertEquals(self::class, $element->getName());

		/** @noinspection PhpUnhandledExceptionInspection exists */
		$parser = new Parser(new Reflection_Property($this, 'array'), 'non-negative-int[][]');
		$type   = $parser->parse();
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(2, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Named::class, $element);
		self::assertEquals('non-negative-int', $element->getName());

		/** @noinspection PhpUnhandledExceptionInspection exists */
		$parser = new Parser(new Reflection_Property($this, 'array'), 'bad-type[][][]');
		$type   = $parser->parse();
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(3, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Undefined::class, $element);
	}

}
