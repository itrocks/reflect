<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface\Reflection;

trait Single
{

	//-------------------------------------------------------------------------------------------- of
	public static function of(Reflection $reflection) : ?static
	{
		/** @noinspection PhpUnhandledExceptionInspection static */
		return $reflection->getAttribute(static::class)?->newInstance();
	}

}
