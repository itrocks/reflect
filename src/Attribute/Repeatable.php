<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface\Reflection;

trait Repeatable
{

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return list<static>
	 */
	public static function of(Reflection $reflection) : array
	{
		$instances = [];
		/** @var int-mask-of<Reflection::T_ALL> $all_flag May be Reflection_Class_Component::T_ALL */
		$all_flag = $reflection::T_ALL;
		foreach ($reflection->getAttributes(static::class, $all_flag) as $attribute) {
			/** @noinspection PhpUnhandledExceptionInspection static */
			$instances[] = $attribute->newInstance();
		}
		return $instances;
	}

}
