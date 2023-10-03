<?php
namespace ITRocks\Reflect\Tests\Attribute;

use Attribute;
use ITRocks\Reflect\Attribute\Override;
use PHPUnit\Framework\TestCase;

class Override_Test extends TestCase
{

	//------------------------------------------------------------------------------- testConstructor
	public function testConstructor() : void
	{
		$override = new Override('property_name', new Attribute, Attribute::class);
		$actual   = [$override->property_name];
		foreach ($override->overrides as $value) {
			$actual[] = is_string($value)
				? ('string:' . $value)
				: ('object:' . get_class($value));
		}
		$expected = ['property_name', 'object:Attribute', 'string:Attribute'];
		self::assertEquals($expected, $actual);
	}

}
