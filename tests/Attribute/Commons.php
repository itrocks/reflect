<?php
namespace ITRocks\Reflect\Tests\Attribute;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Tests\Attribute\Data\Foo;

trait Commons
{

	//---------------------------------------------------------------------- getDeclaringClassCommons
	/** @param list<Reflection_Attribute<Reflection,object>> $attributes */
	private function getDeclaringClassCommons(array $attributes, string $namespace) : void
	{
		foreach ($attributes as $attribute) {
			$argument = $attribute->getArguments()[0];
			$awaited  = $namespace . '\\' . $argument;
			if (str_ends_with($awaited, '\\C1') || str_ends_with($awaited, '\\C2')) {
				$awaited = substr($awaited, 0, -1);
			}
			self::assertEquals(
				$awaited, $attribute->getDeclaringClass(true)->getName(), $argument . ' trait'
			);
			$awaited = rtrim(str_replace(['1', '2'], '', $awaited), 'IT');
			self::assertEquals(
				$awaited, $attribute->getDeclaringClass()->getName(), $argument . ' class'
			);
		}
	}

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		class_exists(Foo::class);
	}

}
